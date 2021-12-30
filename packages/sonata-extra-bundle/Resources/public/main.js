
var adminTimezoneCookie = 'adminUserTimezone=' + Intl.DateTimeFormat().resolvedOptions().timeZone;
if (location.protocol === 'https:') {
    adminTimezoneCookie = adminTimezoneCookie + '; secure'
}
document.cookie = adminTimezoneCookie;
