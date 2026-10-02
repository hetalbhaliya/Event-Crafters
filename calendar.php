<?php

session_start();

include "config/db.php";


/* =========================================================
   GET EVENTS FROM DATABASE
========================================================= */

$sql = "
    SELECT
        id,
        event_name,
        category,
        event_date,
        event_time,
        venue,
        price,
        image
    FROM events
    ORDER BY event_date ASC
";


$result = mysqli_query(
    $conn,
    $sql
);


/* =========================================================
   CREATE EVENT ARRAY
========================================================= */

$events = array();


if ($result) {

    while ($event = mysqli_fetch_assoc($result)) {

        $events[] = array(

            "id"       => (int)$event['id'],

            "name"     => $event['event_name'],

            "category" => $event['category'],

            "date"     => $event['event_date'],

            "time"     => $event['event_time'],

            "venue"    => $event['venue'],

            "price"    => (float)$event['price'],

            "image"    => $event['image']

        );

    }

}


/* =========================================================
   HEADER
========================================================= */

include "includes/header.php";

include "includes/navbar.php";

?>


<!-- =========================================================
     CALENDAR CSS
========================================================= -->

<link
    rel="stylesheet"
    href="assets/css/calendar.css"
>


<!-- =========================================================
     CALENDAR PAGE
========================================================= -->

<section class="calendar-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="calendar-header">

        <span class="calendar-label">
            EVENT CRAFTERS
        </span>

        <h1>
            Event Calendar
        </h1>

        <p>
            Explore upcoming events and plan your special moments.
        </p>

    </div>


    <!-- =====================================================
         SEARCH + CATEGORY
    ====================================================== -->

    <div class="calendar-controls">


        <!-- SEARCH -->

        <div class="calendar-search">

            <i class="fas fa-search"></i>

            <input
                type="text"
                id="eventSearch"
                placeholder="Search events..."
            >

        </div>


        <!-- CATEGORY -->

        <select id="categoryFilter">

            <option value="all">
                All Categories
            </option>


            <?php

            $categories = array();


            foreach ($events as $event) {

                if (
                    !in_array(
                        $event['category'],
                        $categories
                    )
                ) {

                    $categories[] =
                        $event['category'];

                }

            }


            foreach ($categories as $category) {

            ?>

                <option
                    value="<?php
                        echo htmlspecialchars(
                            strtolower($category)
                        );
                    ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $category
                    );

                    ?>

                </option>

            <?php

            }

            ?>

        </select>

    </div>


    <!-- =====================================================
         CALENDAR CARD
    ====================================================== -->

    <div class="calendar-card">


        <!-- =================================================
             CALENDAR TOP
        ================================================== -->

        <div class="calendar-top">


            <!-- PREVIOUS -->

            <button
                type="button"
                id="prevMonth"
                class="calendar-nav-btn"
            >

                <i class="fas fa-chevron-left"></i>

            </button>


            <!-- MONTH -->

            <div class="calendar-month">

                <h2 id="monthName"></h2>

            </div>


            <!-- NEXT -->

            <button
                type="button"
                id="nextMonth"
                class="calendar-nav-btn"
            >

                <i class="fas fa-chevron-right"></i>

            </button>

        </div>


        <!-- =================================================
             TODAY
        ================================================== -->

        <button
            type="button"
            id="todayBtn"
            class="today-btn"
        >

            <i class="fas fa-calendar-day"></i>

            Today

        </button>


        <!-- =================================================
             WEEK DAYS
        ================================================== -->

        <div class="calendar-weekdays">

            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>

        </div>


        <!-- =================================================
             CALENDAR DAYS
        ================================================== -->

        <div
            class="calendar-days"
            id="calendarDays"
        ></div>


    </div>


    <!-- =====================================================
         EVENT MODAL
    ====================================================== -->

    <div
        class="event-modal"
        id="eventModal"
    >

        <div class="event-modal-content">


            <!-- CLOSE -->

            <button
                type="button"
                class="modal-close"
                id="closeModal"
            >

                <i class="fas fa-times"></i>

            </button>


            <!-- MODAL CONTENT -->

            <div id="modalBody"></div>

        </div>

    </div>


</section>


<!-- =========================================================
     EVENTS DATA
========================================================= -->

<script>

const calendarEvents =

<?php

echo json_encode(
    $events,
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

?>;


/* =========================================================
   CURRENT DATE
========================================================= */

let currentDate = new Date();


/* =========================================================
   ELEMENTS
========================================================= */

const calendarDays =
    document.getElementById(
        "calendarDays"
    );


const monthName =
    document.getElementById(
        "monthName"
    );


const searchInput =
    document.getElementById(
        "eventSearch"
    );


const categoryFilter =
    document.getElementById(
        "categoryFilter"
    );


const eventModal =
    document.getElementById(
        "eventModal"
    );


const modalBody =
    document.getElementById(
        "modalBody"
    );


const closeModal =
    document.getElementById(
        "closeModal"
    );


/* =========================================================
   MONTH NAMES
========================================================= */

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


/* =========================================================
   RENDER CALENDAR
========================================================= */

function renderCalendar() {


    /* CLEAR */

    calendarDays.innerHTML = "";


    /* YEAR */

    const year =
        currentDate.getFullYear();


    /* MONTH */

    const month =
        currentDate.getMonth();


    /* MONTH TITLE */

    monthName.textContent =
        monthNames[month] +
        " " +
        year;


    /* FIRST DAY */

    const firstDay =
        new Date(
            year,
            month,
            1
        ).getDay();


    /* LAST DATE */

    const lastDate =
        new Date(
            year,
            month + 1,
            0
        ).getDate();


    /* TODAY */

    const today =
        new Date();


    /* =====================================================
       EMPTY BOXES
    ====================================================== */

    for (
        let i = 0;
        i < firstDay;
        i++
    ) {


        const emptyDay =
            document.createElement(
                "div"
            );


        emptyDay.className =
            "calendar-day empty";


        calendarDays.appendChild(
            emptyDay
        );

    }


    /* =====================================================
       CREATE DAYS
    ====================================================== */

    for (
        let day = 1;
        day <= lastDate;
        day++
    ) {


        /* DAY BOX */

        const dayBox =
            document.createElement(
                "div"
            );


        dayBox.className =
            "calendar-day";


        /* =================================================
           DATE NUMBER
        ================================================== */

        const dateNumber =
            document.createElement(
                "span"
            );


        dateNumber.className =
            "date-number";


        dateNumber.textContent =
            day;


        dayBox.appendChild(
            dateNumber
        );


        /* =================================================
           TODAY
        ================================================== */

        if (

            day === today.getDate() &&

            month === today.getMonth() &&

            year === today.getFullYear()

        ) {


            dayBox.classList.add(
                "today"
            );

        }


        /* =================================================
           DATE STRING
        ================================================== */

        const monthNumber =
            String(
                month + 1
            ).padStart(
                2,
                "0"
            );


        const dayNumber =
            String(
                day
            ).padStart(
                2,
                "0"
            );


        const dateString =
            year +
            "-" +
            monthNumber +
            "-" +
            dayNumber;


        /* =================================================
           GET EVENTS
        ================================================== */

        const dayEvents =
            getFilteredEvents(
                dateString
            );


        /* =================================================
           SHOW FIRST 2 EVENTS
        ================================================== */

        const visibleEvents =
            dayEvents.slice(
                0,
                2
            );


        visibleEvents.forEach(

            function (event) {


                /* EVENT ITEM */

                const eventItem =
                    document.createElement(
                        "div"
                    );


                eventItem.className =
                    "calendar-event";


                /* =================================================
                   EVENT CONTENT
                ================================================== */

                eventItem.innerHTML = `

                    <span class="event-dot"></span>

                    <span class="event-icon">

                        ${getCategoryIcon(
                            event.category
                        )}

                    </span>

                    <span class="event-name">

                        ${escapeHtml(
                            event.name
                        )}

                    </span>

                `;


                /* =================================================
                   EVENT CLICK
                ================================================== */

                eventItem.addEventListener(

                    "click",

                    function (e) {

                        e.stopPropagation();


                        showEvent(
                            event
                        );

                    }

                );


                /* ADD */

                dayBox.appendChild(
                    eventItem
                );

            }

        );


        /* =================================================
           MORE EVENTS
        ================================================== */

        if (
            dayEvents.length > 2
        ) {


            const moreCount =
                dayEvents.length - 2;


            const moreItem =
                document.createElement(
                    "div"
                );


            moreItem.className =
                "calendar-more";


            moreItem.innerHTML = `

                <i class="fas fa-plus"></i>

                ${moreCount} more

            `;


            /* MORE CLICK */

            moreItem.addEventListener(

                "click",

                function (e) {

                    e.stopPropagation();


                    showDateEvents(

                        dateString,

                        dayEvents

                    );

                }

            );


            dayBox.appendChild(
                moreItem
            );

        }


        /* ADD DAY */

        calendarDays.appendChild(
            dayBox
        );

    }

}


/* =========================================================
   FILTER EVENTS
========================================================= */

function getFilteredEvents(
    date
) {


    /* SEARCH VALUE */

    const search =
        searchInput.value
            .toLowerCase()
            .trim();


    /* CATEGORY */

    const category =
        categoryFilter.value
            .toLowerCase();


    return calendarEvents.filter(

        function (event) {


            /* DATE */

            if (
                event.date !== date
            ) {

                return false;

            }


            /* EVENT NAME */

            const eventName =
                event.name
                    .toLowerCase();


            /* EVENT CATEGORY */

            const eventCategory =
                event.category
                    .toLowerCase();


            /* SEARCH */

            if (

                search !== "" &&

                !eventName.includes(
                    search
                )

            ) {

                return false;

            }


            /* CATEGORY */

            if (

                category !== "all" &&

                eventCategory !== category

            ) {

                return false;

            }


            return true;

        }

    );

}


/* =========================================================
   CATEGORY ICON
========================================================= */

function getCategoryIcon(
    category
) {


    const value =
        category
            .toLowerCase();


    /* BIRTHDAY */

    if (

        value.includes(
            "birthday"
        )

    ) {

        return "🎂";

    }


    /* WEDDING */

    if (

        value.includes(
            "wedding"
        ) ||

        value.includes(
            "marriage"
        )

    ) {

        return "💍";

    }


    /* PARTY */

    if (

        value.includes(
            "party"
        ) ||

        value.includes(
            "celebration"
        )

    ) {

        return "🎉";

    }


    /* CORPORATE */

    if (

        value.includes(
            "corporate"
        ) ||

        value.includes(
            "business"
        )

    ) {

        return "💼";

    }


    /* COLLEGE */

    if (

        value.includes(
            "college"
        ) ||

        value.includes(
            "education"
        )

    ) {

        return "🎓";

    }


    /* BABY SHOWER */

    if (

        value.includes(
            "baby"
        ) ||

        value.includes(
            "shower"
        )

    ) {

        return "🍼";

    }


    /* DEFAULT */

    return "✨";

}


/* =========================================================
   SHOW SINGLE EVENT
========================================================= */

function showEvent(
    event
) {


    let imageHTML = "";


    /* =====================================================
       IMAGE
    ====================================================== */

    if (

        event.image &&

        event.image !== ""

    ) {


        imageHTML = `

            <div class="modal-image-wrapper">

                <img

                    src="uploads/events/${escapeHtml(
                        event.image
                    )}"

                    alt="${escapeHtml(
                        event.name
                    )}"

                    class="modal-event-image"

                    onerror="this.style.display='none';"

                >

                <div class="modal-image-overlay"></div>

            </div>

        `;

    }


    /* =====================================================
       EVENT DETAILS
    ====================================================== */

    modalBody.innerHTML = `

        ${imageHTML}


        <div class="modal-event-details">


            <!-- CATEGORY -->

            <span class="modal-event-category">

                <span class="modal-category-icon">

                    ${getCategoryIcon(
                        event.category
                    )}

                </span>

                ${escapeHtml(
                    event.category
                )}

            </span>


            <!-- TITLE -->

            <h2>

                ${escapeHtml(
                    event.name
                )}

            </h2>


            <!-- INFO -->

            <div class="modal-info">


                <!-- DATE -->

                <div class="modal-info-item">

                    <div class="modal-info-icon">

                        <i class="fas fa-calendar"></i>

                    </div>

                    <div>

                        <small>
                            Event Date
                        </small>

                        <strong>

                            ${formatDate(
                                event.date
                            )}

                        </strong>

                    </div>

                </div>


                <!-- TIME -->

                <div class="modal-info-item">

                    <div class="modal-info-icon">

                        <i class="fas fa-clock"></i>

                    </div>

                    <div>

                        <small>
                            Event Time
                        </small>

                        <strong>

                            ${escapeHtml(
                                event.time
                            )}

                        </strong>

                    </div>

                </div>


                <!-- VENUE -->

                <div class="modal-info-item">

                    <div class="modal-info-icon">

                        <i class="fas fa-location-dot"></i>

                    </div>

                    <div>

                        <small>
                            Venue
                        </small>

                        <strong>

                            ${escapeHtml(
                                event.venue
                            )}

                        </strong>

                    </div>

                </div>


            </div>


            <!-- PRICE -->

            <div class="modal-price-box">

                <span>
                    Event Price
                </span>

                <strong>

                    ₹${Number(
                        event.price
                    ).toLocaleString(

                        "en-IN",

                        {

                            minimumFractionDigits: 2,

                            maximumFractionDigits: 2

                        }

                    )}

                </strong>

                <small>
                    per person
                </small>

            </div>


            <!-- VIEW EVENT -->

            <a

                href="event-details.php?id=${event.id}"

                class="modal-view-btn"

            >

                View Event

                <i class="fas fa-arrow-right"></i>

            </a>


        </div>

    `;


    /* SHOW */

    eventModal.classList.add(
        "show"
    );

}


/* =========================================================
   SHOW ALL EVENTS OF DATE
========================================================= */

function showDateEvents(
    date,
    events
) {


    let eventsHTML = "";


    /* =====================================================
       CREATE EVENT LIST
    ====================================================== */

    events.forEach(

        function (event) {


            eventsHTML += `

                <div

                    class="date-event-card"

                    data-event-id="${event.id}"

                >


                    <div class="date-event-icon">

                        ${getCategoryIcon(
                            event.category
                        )}

                    </div>


                    <div class="date-event-info">


                        <strong>

                            ${escapeHtml(
                                event.name
                            )}

                        </strong>


                        <span>

                            ${escapeHtml(
                                event.category
                            )}

                        </span>


                        <small>

                            <i class="fas fa-clock"></i>

                            ${escapeHtml(
                                event.time
                            )}

                        </small>


                    </div>


                    <i class="fas fa-chevron-right"></i>


                </div>

            `;

        }

    );


    /* =====================================================
       MODAL CONTENT
    ====================================================== */

    modalBody.innerHTML = `

        <div class="date-events-modal">


            <div class="date-events-heading">

                <span>

                    ${formatDate(
                        date
                    )}

                </span>


                <h2>

                    Events on this day

                </h2>


                <p>

                    ${events.length}

                    event${
                        events.length !== 1
                            ? "s"
                            : ""
                    }

                    scheduled

                </p>

            </div>


            <div class="date-events-list">

                ${eventsHTML}

            </div>


        </div>

    `;


    /* =====================================================
       ADD CLICK TO EVENT CARDS
    ====================================================== */

    document

        .querySelectorAll(
            ".date-event-card"
        )

        .forEach(

            function (card) {


                card.addEventListener(

                    "click",

                    function () {


                        const eventId =
                            parseInt(
                                this.dataset.eventId
                            );


                        const event =
                            calendarEvents.find(

                                function (item) {

                                    return (

                                        item.id ===
                                        eventId

                                    );

                                }

                            );


                        if (event) {

                            showEvent(
                                event
                            );

                        }

                    }

                );

            }

        );


    /* SHOW MODAL */

    eventModal.classList.add(
        "show"
    );

}


/* =========================================================
   CLOSE MODAL
========================================================= */

closeModal.addEventListener(

    "click",

    function () {

        eventModal.classList.remove(
            "show"
        );

    }

);


/* =========================================================
   CLICK OUTSIDE MODAL
========================================================= */

eventModal.addEventListener(

    "click",

    function (e) {

        if (
            e.target === eventModal
        ) {

            eventModal.classList.remove(
                "show"
            );

        }

    }

);


/* =========================================================
   ESC KEY
========================================================= */

document.addEventListener(

    "keydown",

    function (e) {

        if (
            e.key === "Escape"
        ) {

            eventModal.classList.remove(
                "show"
            );

        }

    }

);


/* =========================================================
   PREVIOUS MONTH
========================================================= */

document

    .getElementById(
        "prevMonth"
    )

    .addEventListener(

        "click",

        function () {


            currentDate.setMonth(

                currentDate.getMonth() - 1

            );


            renderCalendar();

        }

    );


/* =========================================================
   NEXT MONTH
========================================================= */

document

    .getElementById(
        "nextMonth"
    )

    .addEventListener(

        "click",

        function () {


            currentDate.setMonth(

                currentDate.getMonth() + 1

            );


            renderCalendar();

        }

    );


/* =========================================================
   TODAY
========================================================= */

document

    .getElementById(
        "todayBtn"
    )

    .addEventListener(

        "click",

        function () {


            currentDate =
                new Date();


            renderCalendar();

        }

    );


/* =========================================================
   SEARCH
========================================================= */

searchInput.addEventListener(

    "input",

    function () {

        renderCalendar();

    }

);


/* =========================================================
   CATEGORY FILTER
========================================================= */

categoryFilter.addEventListener(

    "change",

    function () {

        renderCalendar();

    }

);


/* =========================================================
   FORMAT DATE
========================================================= */

function formatDate(
    dateString
) {


    const date =
        new Date(

            dateString +
            "T00:00:00"

        );


    return date.toLocaleDateString(

        "en-IN",

        {

            day: "2-digit",

            month: "short",

            year: "numeric"

        }

    );

}


/* =========================================================
   ESCAPE HTML
========================================================= */

function escapeHtml(
    value
) {


    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        value ?? "";


    return div.innerHTML;

}


/* =========================================================
   INITIAL LOAD
========================================================= */

renderCalendar();

</script>


<?php

include "includes/footer.php";

?>