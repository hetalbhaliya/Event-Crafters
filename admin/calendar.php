

<?php

session_start();

/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: index.php");
    exit;

}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";


/* =========================================
   GET EVENTS
========================================= */

$events = [];

$query = mysqli_query(
    $conn,
    "SELECT id, event_name, category, venue, event_date, event_time
     FROM events
     ORDER BY event_date ASC"
);

if ($query) {

    while ($row = mysqli_fetch_assoc($query)) {

        $events[] = $row;

    }

}

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-calendar.css"
>


<!-- =========================================
     ADMIN CALENDAR
========================================= -->

<section class="admin-calendar">

    <div class="admin-calendar-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-heading">

            <span>
                EVENT MANAGEMENT
            </span>

            <h1>
                Event Calendar
            </h1>

            <p>
                View all scheduled events according
                to their dates.
            </p>

        </div>


        <!-- =====================================
             BACK BUTTON
        ====================================== -->

        <a
            href="dashboard.php"
            class="back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Dashboard

        </a>


        <!-- =====================================
             CALENDAR
        ====================================== -->

        <div class="calendar-box">

            <div class="calendar-header">

                <button
                    type="button"
                    id="prevMonth"
                    class="calendar-nav-btn"
                >

                    <i class="fas fa-chevron-left"></i>

                </button>


                <h2 id="calendarMonth">
                    Loading...
                </h2>


                <button
                    type="button"
                    id="nextMonth"
                    class="calendar-nav-btn"
                >

                    <i class="fas fa-chevron-right"></i>

                </button>

            </div>


            <!-- DAYS -->

            <div class="calendar-weekdays">

                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>

            </div>


            <!-- DATES -->

            <div
                class="calendar-days"
                id="calendarDays"
            ></div>

        </div>


        <!-- =====================================
             EVENT LIST
        ====================================== -->

        <div class="calendar-events-section">

            <div class="admin-section-heading">

                <span>
                    SCHEDULED EVENTS
                </span>

                <h2>
                    Upcoming Events
                </h2>

            </div>


            <div class="calendar-event-list">

                <?php if (!empty($events)) { ?>

                    <?php foreach ($events as $event) { ?>

                        <div class="calendar-event-card">

                            <div class="calendar-event-date">

                                <strong>

                                    <?php
                                    echo date(
                                        "d",
                                        strtotime($event['event_date'])
                                    );
                                    ?>

                                </strong>

                                <span>

                                    <?php
                                    echo date(
                                        "M",
                                        strtotime($event['event_date'])
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="calendar-event-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $event['event_name']
                                    );
                                    ?>

                                </h3>


                                <p>

                                    <i class="fas fa-tag"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $event['category']
                                    );
                                    ?>

                                </p>


                                <p>

                                    <i class="fas fa-location-dot"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $event['venue']
                                    );
                                    ?>

                                </p>


                                <p>

                                    <i class="fas fa-clock"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $event['event_time']
                                    );
                                    ?>

                                </p>

                            </div>


                            <div class="calendar-event-action">

                                <a
                                    href="edit-event.php?id=<?php echo $event['id']; ?>"
                                    class="calendar-view-btn"
                                >

                                    Manage

                                    <i class="fas fa-arrow-right"></i>

                                </a>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="calendar-empty">

                        <i class="fas fa-calendar-xmark"></i>

                        <h3>
                            No Events Found
                        </h3>

                        <p>
                            There are no events scheduled yet.
                        </p>

                        <a
                            href="add-event.php"
                            class="calendar-add-btn"
                        >

                            <i class="fas fa-plus"></i>

                            Add Event

                        </a>

                    </div>

                <?php } ?>

            </div>

        </div>


    </div>

</section>


<!-- =========================================
     CALENDAR JAVASCRIPT
========================================= -->

<script>

const events = <?php echo json_encode($events); ?>;

let currentDate = new Date();


function renderCalendar() {

    const year = currentDate.getFullYear();

    const month = currentDate.getMonth();


    const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];


    document.getElementById("calendarMonth").innerText =
        monthNames[month] + " " + year;


    const firstDay =
        new Date(year, month, 1).getDay();


    const daysInMonth =
        new Date(year, month + 1, 0).getDate();


    const calendarDays =
        document.getElementById("calendarDays");


    calendarDays.innerHTML = "";


    /* EMPTY DAYS */

    for (let i = 0; i < firstDay; i++) {

        const emptyDay =
            document.createElement("div");

        emptyDay.className =
            "calendar-day empty";

        calendarDays.appendChild(emptyDay);

    }


    /* ACTUAL DAYS */

    for (let day = 1; day <= daysInMonth; day++) {

        const dayBox =
            document.createElement("div");

        dayBox.className =
            "calendar-day";


        const dateNumber =
            document.createElement("span");

        dateNumber.className =
            "calendar-date-number";

        dateNumber.innerText =
            day;


        dayBox.appendChild(dateNumber);


        /* FORMAT DATE */

        const monthNumber =
            String(month + 1).padStart(2, "0");

        const dayNumber =
            String(day).padStart(2, "0");


        const dateString =
            year + "-" +
            monthNumber + "-" +
            dayNumber;


        /* FIND EVENTS */

        const dayEvents =
            events.filter(function(event) {

                return event.event_date === dateString;

            });


        /* EVENT DOT */

        if (dayEvents.length > 0) {

            dayBox.classList.add(
                "has-event"
            );


            const eventDot =
                document.createElement("span");

            eventDot.className =
                "event-dot";


            dayBox.appendChild(
                eventDot
            );


            /* EVENT COUNT */

            if (dayEvents.length > 1) {

                const eventCount =
                    document.createElement("small");

                eventCount.className =
                    "event-count";

                eventCount.innerText =
                    dayEvents.length + " events";

                dayBox.appendChild(
                    eventCount
                );

            } else {

                const eventName =
                    document.createElement("small");

                eventName.className =
                    "event-name";

                eventName.innerText =
                    dayEvents[0].event_name;

                dayBox.appendChild(
                    eventName
                );

            }

        }


        calendarDays.appendChild(
            dayBox
        );

    }

}


/* =========================================
   PREVIOUS MONTH
========================================= */

document.getElementById("prevMonth")
.addEventListener(
    "click",
    function() {

        currentDate.setMonth(
            currentDate.getMonth() - 1
        );

        renderCalendar();

    }
);


/* =========================================
   NEXT MONTH
========================================= */

document.getElementById("nextMonth")
.addEventListener(
    "click",
    function() {

        currentDate.setMonth(
            currentDate.getMonth() + 1
        );

        renderCalendar();

    }
);


/* =========================================
   LOAD CALENDAR
========================================= */

renderCalendar();

</script>


<?php

include "../includes/footer.php";

?>