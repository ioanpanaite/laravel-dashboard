<?php

use custom\helpers\Responder;
use custom\helpers\Midrepo;
use custom\exceptions\ApiException;

/**
 * Class MeetingController
 */
class MeetingController extends BaseController
{

    /**
     * @param $meetingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);

        if ($meeting->user_id != Auth::user()->id) {
            return Responder::json(false)->send();

        }
        $meeting->delete();

        return Responder::json(true)->send();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive()
    {
        if (Midrepo::getOrFail('meeting_role') == MEETING_ROLE_CREATOR) {
            $meeting           = Midrepo::getOrFail('meeting');
            $meeting->archived = !$meeting->archived;
            $meeting->save();

            return Responder::json(true)->withData($meeting->archived)->send();
        }

        return Responder::json(false)->send();

    }

    /**
     * @param $meetingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($meetingId)
    {
        $meetings = Meeting::myMeetings()->where('id', $meetingId)->Complete()->first();

        return Responder::json(true)->withDataTransform($meetings, 'MeetingTransformer')->send();

    }

    /**
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function getList()
    {

        $meetings = Meeting::myMeetings();

        if (Input::has('content_id')) {
            $meetings = $meetings->where('content_id', Input::get('content_id'));
        }

        if (Input::has('meeting_id')) {

            $meeting = $meetings->where('id', Input::get('meeting_id'));
        }

        if (Input::has('space_code')) {
            $space = Space::getByCode(Input::get('space_code'));

            if (Auth::user()->inSpace($space->id) < ROLE_MEMBER) {
                return Responder::json(false)->withMessage('access_denied');
            }

            $meetings = $meetings->where('space_id', $space->id);
        }

        $meetings->where('archived', Input::get('archived', 0));

        $meetings->Complete();

        return Responder::json(true)->withDataTransform($meetings->get(), 'MeetingTransformer')->send();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCounters()
    {
        if (Input::has('contentId')) {
            $count = DB::table('meetings')
                ->where('archived', 0)
                ->where('content_id', Input::get('contentId'))
                ->where('state', '<', 3)->count();

            return Responder::json(true)->withData($count)->send();
        }

        $userId  = Auth::user()->id;
        $spaceId = null;
        if (Input::has('space_code')) {
            $spaceId = Space::getByCode(Input::get('space_code'))->id;
            if (Auth::user()->inSpace($spaceId) < ROLE_MEMBER) {
                return Responder::json(false)->send();
            }
        }

        $tot      = 0;
        $myMeetings  = 0;
        $delMeetings = 0;
        $meetings    = Meeting::myMeetings()
            ->where('archived', 0)
            ->where('state', '<', 3);
        if (isset($spaceId)) {
            $meetings = $meetings->where('space_id', $spaceId);
        }

        $meetings = $meetings->get()->toArray();

        foreach ($meetings as $meeting) {
            $tot++;
            if ($meeting['user_id'] == $userId && count($meeting['assigned_to']) == 0) {
                $myMeetings++;
            }

            if (count($meeting['assigned_to']) > 0 && findWhere($meeting['assigned_to'], ['user_id' => $userId])) {
                $myMeetings++;
            }

            if ($meeting['user_id'] == $userId && count($meeting['assigned_to']) > 0) {
                $f = findWhere($meeting['assigned_to'], ['user_id' => $userId]);
                if (!$f) {
                    $delMeetings++;
                }
                if ($f && count($meeting['assigned_to']) > 1) {
                    $delMeetings++;
                }

            }

        }
        return Responder::json(true)->withData(['total' => $tot, 'my' => $myMeetings, 'delegated' => $delMeetings])->send();

    }

    /**
     * @param $meetingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($meetingId)
    {
        $meeting     = Midrepo::getOrFail('meeting');
        $meetingRole = Midrepo::getOrFail('meeting_role');

        if (!$field = Input::get('field', false)) {
            return Responder::json(false)->send();
        }

        $value = Input::get('value');

        if ($meeting->updateField($field, $value, $meetingRole)) {
            return Responder::json(true)->send();
        }

        if ($field == 'due_date' && $meetingRole == MEETING_ROLE_CREATOR) {
            $meeting->syncCalendar($value);
            $meeting->load('assignedTo')->load('calendar');
            return Responder::json(true)->withDataTransform($meeting, 'MeetingTransformer')->send();
        }

        if ($field == 'assigned_to' && $meetingRole == MEETING_ROLE_CREATOR) {
            $meeting->assignSync($value);
            $meeting->load('assignedTo')->load('calendar');
            return Responder::json(true)->withDataTransform($meeting, 'MeetingTransformer')->send();
        }

        return Responder::json(false)->send();

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws custom\exceptions\ApiException
     */
    public function store()
    {

        $meeting = new Meeting;

        $meeting->user_id     = Auth::user()->id;
        $meeting->description = Input::get('description', '');
        $meeting->title       = Input::get('title', '');
        $meeting->state       = Input::get('state', 0);
        $meeting->priority    = Input::get('priority', 1);
        $meeting->archived    = 0;
        $meeting->history     = json_encode(
            ['date' => date('Y-m-d H:i:s'), 'user' => Auth::user()->full_name, 'txt' => ' meeting created.']
        );
        if (Input::has('content_id')) {
            $content = Content::findOrFail(Input::get('content_id'));
            if (Auth::user()->inSpace($content->space_id) >= ROLE_MEMBER) {
                $meeting->content_id = $content->id;
                $meeting->space_id   = $content->space_id;
            }
        }

        if (Input::has('space_code')) {
            $spaceId = Space::getByCode(Input::get('space_code'))->id;
            if (Auth::user()->inSpace($spaceId) >= ROLE_MEMBER) {
                $meeting->space_id = $spaceId;
            } else {
                return Responder::json(false)->withMessage('access_denied')->send();
            }
        }

        if (!$meeting->withValidation()->save()) {
            throw new ApiException($meeting->validator);
        }


        if (Input::has('due_date')) {
            $meeting->syncCalendar(Input::get('due_date'));
        }

        if (Input::has('assigned_to')) {
            $meeting->assignSync(Input::get('assigned_to'));
        }

        Attachment::processInput('Meeting', $meeting->id);

        $meeting->load('user')->load('assignedTo')->load('attachments')->load('comments')->load('calendar');

        return Responder::json(true)->withDataTransform($meeting, 'MeetingTransformer')->send();

    }

}