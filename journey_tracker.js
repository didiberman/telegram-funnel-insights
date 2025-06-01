/********************************************************************************
 * journey_tracker.js (MODIFIED - Allow anonymous page view notifications)
 * Client-side script for tracking user journey, managing cookies, and interacting with backend.
 ********************************************************************************/

// --- Cookie Helper Functions ---
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (encodeURIComponent(value) || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == " ") c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function getUrlParameter(name) {
    name = name.replace(/[\[]/, "\\\\[").replace(/[\]]/, "\\\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// --- Function to Populate Hidden Fields in Journal Forms ---
function populateJournalFormHiddenFields(dayNumber) {
    console.log("journey_tracker.js: populateJournalFormHiddenFields called for day", dayNumber);
    var userTrackerId = getCookie("user_tracker_id");
    var userEmail = getCookie("user_email");
    var v_variable_cookie = getCookie("v_variable");
    var identifierForBackend = userTrackerId || userEmail;

    console.log("journey_tracker.js (populateJournalFormHiddenFields): user_tracker_id:", userTrackerId, "user_email:", userEmail, "v_variable:", v_variable_cookie, "identifierForBackend:", identifierForBackend);

    if (identifierForBackend) {
        var journalForm = document.getElementById("journalFormDay" + dayNumber);
        if (journalForm) {
            console.log("journey_tracker.js (populateJournalFormHiddenFields): Found journal form: journalFormDay" + dayNumber);
            if(journalForm.elements["user_tracker_id"]) {
                journalForm.elements["user_tracker_id"].value = identifierForBackend;
            }
            if(journalForm.elements["v_variable"]) {
                journalForm.elements["v_variable"].value = v_variable_cookie || "N/A";
            }
            if(journalForm.elements["email"]) {
                journalForm.elements["email"].value = userEmail || "";
            }
        } else {
            console.log("journey_tracker.js (populateJournalFormHiddenFields): Journal form journalFormDay" + dayNumber + " not found.");
        }
    } else {
        console.log("journey_tracker.js (populateJournalFormHiddenFields): No identifierForBackend. Cannot populate fields.");
    }
}

// --- Record Page Activity Function (calls update_journey.php) ---
function recordPageActivity(userId, videoId, email, pageViewed) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_journey.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                console.log("journey_tracker.js: Page activity update response:", response);
                if (!response.success) {
                    console.error("journey_tracker.js: Server error updating page activity:", response.message);
                }
            } catch (e) {
                console.error("journey_tracker.js: Error parsing page activity update response:", e, xhr.responseText);
            }
        } else {
            console.error("journey_tracker.js: HTTP error updating page activity. Status:", xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error("journey_tracker.js: Network error during page activity update.");
    };
    var params = "user_tracker_id=" + encodeURIComponent(userId) +
                 "&v_variable=" + encodeURIComponent(videoId) +
                 "&email=" + encodeURIComponent(email) +
                 "&page_viewed=" + encodeURIComponent(pageViewed);
    xhr.send(params);
}

// --- Send Day Page View to tg.php ---
function sendDayPageViewToTg(dayHtmlPageName) {
    if (!dayHtmlPageName || !dayHtmlPageName.startsWith("day") || !dayHtmlPageName.endsWith(".html")) {
        console.log("journey_tracker.js: sendDayPageViewToTg - Invalid page name for tg.php:", dayHtmlPageName);
        return;
    }

    const formattedPageName = dayHtmlPageName.replace(".html", "-view");
    console.log("journey_tracker.js: Attempting to send page view to tg.php. Formatted page name:", formattedPageName);

    var userEmail = getCookie("user_email");
    var userTrackerId = getCookie("user_tracker_id");
    var v_variable = getCookie("v_variable");

    // MODIFIED: Allow sending notification even for anonymous users
    if (!userTrackerId && !userEmail) {
        console.log("journey_tracker.js: sendDayPageViewToTg - User is anonymous (no user_tracker_id or user_email). Sending anonymous page view notification.");
        userEmail = "Anonymous";
        userTrackerId = "anonymous_" + Date.now();
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "tg.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200 || xhr.status === 204) {
            console.log("journey_tracker.js: Successfully sent page view to tg.php for", formattedPageName, "Status:", xhr.status);
        } else {
            console.error("journey_tracker.js: Error sending page view to tg.php for", formattedPageName, "Status:", xhr.status, "Response:", xhr.responseText);
        }
    };
    xhr.onerror = function() {
        console.error("journey_tracker.js: Network error sending page view to tg.php for", formattedPageName);
    };

    var params = "page=" + encodeURIComponent(formattedPageName) +
                 "&email=" + encodeURIComponent(userEmail || "Anonymous") +
                 "&user_tracker_id=" + encodeURIComponent(userTrackerId || "Anonymous") +
                 "&v_variable=" + encodeURIComponent(v_variable || "");
    xhr.send(params);
}

// --- Initialization Logic ---
window.addEventListener("load", function() {
    console.log("journey_tracker.js: window.load event fired.");

    // Additional check for document readiness, though window.onload should suffice.
    if (document.readyState !== "complete") {
        console.warn("journey_tracker.js: window.load fired but document.readyState is not 'complete'. Current state: " + document.readyState + ". Proceeding cautiously.");
    }

    var v_variable_cookie = getCookie("v_variable");
    var v_param_from_url = getUrlParameter("v");
    if (v_param_from_url && v_param_from_url !== v_variable_cookie) {
        setCookie("v_variable", v_param_from_url, 365);
        v_variable_cookie = v_param_from_url;
    }
    if (!v_variable_cookie && v_param_from_url) { // Fallback
        setCookie("v_variable", v_param_from_url, 365);
        v_variable_cookie = v_param_from_url;
    }

    var userTrackerId = getCookie("user_tracker_id");
    var userEmail = getCookie("user_email");
    var identifierForBackend = (userTrackerId && userTrackerId.trim() !== "") ? userTrackerId : ((userEmail && userEmail.trim() !== "") ? userEmail : null);
    console.log("journey_tracker.js: Initial identifierForBackend (on window.load, trimmed check):", identifierForBackend);

    var pathName = window.location.pathname;
    var currentPageName = pathName.substring(pathName.lastIndexOf("/") + 1);

    if (identifierForBackend && (currentPageName === "index.html" || currentPageName.startsWith("day") || currentPageName === "thank_you_final.html")) {
        console.log("journey_tracker.js: Recording page activity for update_journey.php:", identifierForBackend, "on page:", currentPageName);
        setCookie("last_activity_timestamp", new Date().toISOString(), 365);
        recordPageActivity(identifierForBackend, v_variable_cookie || "N/A", userEmail || "", currentPageName);
    } else {
        console.log("journey_tracker.js: Not recording page activity for update_journey.php. identifierForBackend:", identifierForBackend, "currentPageName:", currentPageName);
    }

    // MODIFIED: Send page view notification for day pages regardless of user identification
    if (currentPageName.startsWith("day") && currentPageName.endsWith(".html")) {
        console.log("journey_tracker.js: Detected day page. Scheduling tg.php notification for:", currentPageName, "in 1000ms.");
        setTimeout(function() {
            console.log("journey_tracker.js: Timeout (1000ms) expired. Attempting to send notification to tg.php for:", currentPageName);
            sendDayPageViewToTg(currentPageName); // This function now handles anonymous users
        }, 1000);
    }

    var dayNumberMatch = currentPageName.match(/^day(\d+)\.html$/);
    if (dayNumberMatch) {
        var dayNumber = dayNumberMatch[1];
        populateJournalFormHiddenFields(dayNumber);
    }

    if (!getCookie("legit_visitor")) {
        setCookie("legit_visitor", "yes", 1);
    }
});
