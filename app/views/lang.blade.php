translations = <% json_encode(Lang::get('client')) %>
moment.locale("<% Config::get('app.locale')%>" );
var h_meeting = <% (int)env('HIDE_MEETINGS', false) %>;
var h_tasks = <% (int)env('HIDE_TASKS', false) %>;
var h_cal = <% (int)env('HIDE_CALENDAR', false) %>;
var h_people = <% (int)env('HIDE_PEOPLE', false) %>;
angular.module("ngLocale", [], ["$provide", function($provide) {
var PLURAL_CATEGORY = {ZERO: "zero", ONE: "one", TWO: "two", FEW: "few", MANY: "many", OTHER: "other"};
function getDecimals(n) {
n = n + '';
var i = n.indexOf('.');
return (i == -1) ? 0 : n.length - i - 1;
}

function getVF(n, opt_precision) {
var v = opt_precision;

if (undefined === v) {
v = Math.min(getDecimals(n), 3);
}

var base = Math.pow(10, v);
var f = ((n * base) | 0) % base;
return {v: v, f: f};
}

$provide.value("$locale", {
"DATETIME_FORMATS":
<% json_encode(Lang::get('formats.DATETIME_FORMATS')) %>

,
"NUMBER_FORMATS": {
"CURRENCY_SYM": "$",
"DECIMAL_SEP": ".",
"GROUP_SEP": ",",
"PATTERNS": [
{
"gSize": 3,
"lgSize": 3,
"maxFrac": 3,
"minFrac": 0,
"minInt": 1,
"negPre": "-",
"negSuf": "",
"posPre": "",
"posSuf": ""
},
{
"gSize": 3,
"lgSize": 3,
"maxFrac": 2,
"minFrac": 2,
"minInt": 1,
"negPre": "\u00a4-",
"negSuf": "",
"posPre": "\u00a4",
"posSuf": ""
}
]
},
"id": "en-001",
"pluralCat": function(n, opt_precision) {  var i = n | 0;  var vf = getVF(n, opt_precision);  if (i == 1 && vf.v == 0) {    return PLURAL_CATEGORY.ONE;  }  return PLURAL_CATEGORY.OTHER;}
});
}]);