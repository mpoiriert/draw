$(function () {
    $('body').append('<div style="display: none" id="dialog-session" title="Your session is about to expire.">\n' +
        '  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Do you want to keep your session alive? (<span id="session-seconds"></span> left)</p>\n' +
        '</div>');
});

function SessionExpirationHandler(delay, keepAliveUrl, expiredUrl) {
    this.delay = delay;

    this.relocate = function () {
        window.location = expiredUrl;
    };

    this.isExpired = function () {
        return this.getDelayBeforeExpiration() <= 0;
    };

    this.promptSession = function () {
        const self = this;
        $('#dialog-session').dialog({
            resizable: false,
            height: 'auto',
            width: 400,
            modal: true,
            buttons: {
                'Keep alive': function () {
                    $.get(keepAliveUrl);
                    self.initializeCheck();
                    $(this).dialog('close');
                }
            }
        });
    };

    this.initializeCheck = function () {
        this.now = Math.round(new Date().valueOf() / 1000);
        const self = this;

        setTimeout(
            function () {
                self.promptSession();
            },
            (this.delay - 60) * 1000
        );

        setTimeout(
            function () {
                if (self.isExpired()) {
                    self.relocate();
                }
            },
            (this.delay + 2) * 1000
        );
    };

    this.getDelayBeforeExpiration = function () {
        return Math.max(0, this.delay - (Math.round(new Date().valueOf() / 1000) - this.now));
    };

    const self = this;

    setInterval(function () {
        const delay = self.getDelayBeforeExpiration();
        const text = delay + ' second' + (delay > 1 ? 's' : '');
        $('#session-seconds').text(text);
    }, 1000);
    this.initializeCheck();
}