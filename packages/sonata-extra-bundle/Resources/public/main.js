
function setTimezoneCookie(environment) {
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    var languageCookie = 'timezone=' + timeZone + ';path=/admin';
    if (environment !== 'local') {
        languageCookie = languageCookie + '; secure'
    }
    document.cookie = languageCookie;
}