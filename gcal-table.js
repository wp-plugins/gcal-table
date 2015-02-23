window.gcalTable = (function () {

    var tableClass = 'gcal-table';

    var filters = {
        oldEvents: function (event) {
            return Date.now() < (new Date(Date.parse(event.end.dateTime))).getTime();
        }, sortDate: function (a, b) {
            var s = new Date(Date.parse(a.start.dateTime)),
                e = new Date(Date.parse(b.start.dateTime));
            return s > e ? 1 : s < e ? -1 : 0;
        }
    };


    function createTimeString(start, end) {
        var s = new Date(Date.parse(start.dateTime)),
            e = new Date(Date.parse(end.dateTime));

        var isSameDay = (s.getDate() == e.getDate()
        && s.getMonth() == e.getMonth()
        && s.getFullYear() == e.getFullYear());

        //setting start end end date variables for easier formating ( end-date have _ prefix  )
        // ('0' + VARIABLE).slice(-2)  adds zero for numbers 0-9 in front
        var D = ('0' + s.getDate()).slice(-2),
            M = ('0' + (s.getMonth() + 1)).slice(-2),
            Y = s.getFullYear(),
            h = s.getHours(),
            m = ('0' + s.getMinutes()).slice(-2),
            _D = e.getDate(),
            _M = (e.getMonth() + 1),
            _h = e.getHours(),
            _m = ('0' + e.getMinutes()).slice(-2);

        if (isSameDay) {
            return D + "." + M + "." + Y + " <br /> " + h + ":" + m + " - " + _h + ":" + _m;
        }
        return D + "." + M + " - " + _D + "." + _M + "  " + h + ":" + m + " - " + _h + ":" + _m;
    }

    function tableAppend(root, text) {
        var cell = document.createElement('div');
        cell.className = "cell";
        cell.appendChild(document.createTextNode(text));
        root.appendChild(cell);
    }

    /**
     * creates break-line elements from text, that would get escaped
     */
    function tableAppendLB(root, text) {
        var cell = document.createElement('div');
        cell.className = "cell";
        var t = text.split(/\s*<br ?\/?>\s*/i);

        if (t[0].length > 0) {
            cell.appendChild(document.createTextNode(t[0]));
        }
        for (var i = 1; i < t.length; i++) {
            cell.appendChild(document.createElement('br'));
            if (t[i].length > 0) {
                cell.appendChild(document.createTextNode(t[i]));
            }
        }
        root.appendChild(cell);
    }

    function buildTalbe(events) {
        var oldTable = document.getElementsByClassName(tableClass)[0];
        var newTable = oldTable.cloneNode(true);

        for (var i = 0; i < events.length; i++) {
            var time = createTimeString(events[i].start, events[i].end),
                rowDesc = document.createElement('div'),
                row = document.createElement('div');
            row.className = "row event";
            rowDesc.className = "dscrbtn";

            tableAppendLB(row, time);
            tableAppend(row, events[i].summary);
            tableAppend(row, events[i].location);

            newTable.appendChild(row);

            if (events[i].description) {
                tableAppend(rowDesc, events[i].description);
                newTable.appendChild(rowDesc);
            }
        }

        oldTable.parentNode.replaceChild(newTable, oldTable);
    }

    function addTableListener() {
        jQuery('.' + tableClass + ' .row').on('click', function (e) {
            if (jQuery(this).hasClass('active')) {
                jQuery(this).removeClass('active');
            } else {
                jQuery(this).siblings('.row').removeClass('active');
                jQuery(this).addClass('active');
            }
        });
    }

    function main() {
        var table = jQuery('.' + tableClass),
            calId = table.data('cal-id'),
            eventCount = table.data('event-count'),
            apiKey = table.data('api-key') || 'AIzaSyApV8MC2YBpqQW-2ilWHN2QIEnul7SJbFA';

        if (typeof calId == 'undefined') {
            console.log("no calendar id specified");
        } else {
            jQuery.ajax({
                type: 'GET',
                url: encodeURI('https://www.googleapis.com/calendar/v3/calendars/' + calId + '/events?key=' + apiKey),
                dataType: 'json',
                success: function (response) {
                    var events = response.items.filter(filters.oldEvents);
                    events = events.sort(filters.sortDate);
                    if (typeof eventCount != 'undefined' && eventCount < events.length) {
                        events = events.slice(0, eventCount);
                    }
                    buildTalbe(events);
                    addTableListener();
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    }

    main();
}());