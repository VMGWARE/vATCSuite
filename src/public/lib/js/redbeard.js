$(document).ready(function () {
    // This code attaches an event listener to all input and textarea elements.
    // It listens for the "input" event, which occurs when the user types or pastes content into the elements.
    $("input, textarea").on("input", function () {
        // Store the current cursor position (caret position) within the input or textarea element.
        var t = this.selectionStart,
            // Regular expression to match characters that are not alphanumeric (0-9, a-z),
            // comma, period, hyphen, forward slash, or whitespace.
            o = /[^0-9a-z\,\.\-\/\s]/gi,
            // Get the current value of the input or textarea element.
            a = $(this).val();

        // Check if the value contains any characters that match the regular expression.
        if (o.test(a)) {
            // If there are any matches, replace them with an empty string (remove them from the value).
            $(this).val(a.replace(o, ""));

            // Since characters were removed, adjust the cursor position to maintain the user's input position.
            t--;
        }

        // Check if the input element is not of type "checkbox".
        if (this.type !== "checkbox" && this.type !== "radio") {
            // If it's not a checkbox, set the cursor position to the stored value.
            // This ensures that the cursor position remains unchanged even after the replacement above.
            this.setSelectionRange(t, t);
        }
    });

    /**
     * Copy text to clipboard
     * @param {string} text - The text to copy
     * @returns {void} Nothing
     */
    function copy(text) {
        output = $(text).html();
        navigator.clipboard.writeText(output);
    }

    /**
     * Return a modal with the error message
     * @param message - The error message.
     * @param id - The id of the modal.
     * @returns a string that represents an HTML modal element with a title, body, and a close button.
     */
    function ErrorModal(message, id) {
        return `
        <div class="modal fade show" id="${id}" tabindex="-1" aria-modal="true" role="dialog" style="display: block;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Oops!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="fs-3 text-danger"><i class="fa-solid fa-circle-xmark"></i><br>Generation Failed</p>
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Return a modal with the title and body
     * @param title - The title of the modal
     * @param body - The body of the modal.
     * @param id - The id of the modal.
     * @returns a string that represents an HTML modal element with a title, body, and a close button.
     */
    function Modal(title, body, id) {
        return `
        <div class="modal fade show" id="${id}" tabindex="-1" aria-modal="true" role="dialog" style="display: block;">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        ${body}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    // On click event for the element with id "list-runways"
    $("#list-runways").click(function (t) {
        t.preventDefault(); // Prevent default action of the click event
        $("#loading").show(); // Show the loading element
        icao = $("#icao").val(); // Get the value of the input with id "icao"

        // If icao is empty
        if (icao == "") {
            // Show an error modal with the message "ICAO cannot be empty."
            $("#runway-output").html(
                ErrorModal("ICAO cannot be empty.", "runway-modal")
            );
            $("#runway-modal").modal("show"); // Show the modal with id "runway-modal"
            $("#loading").hide(); // Hide the loading element
            return; // Exit the function
        }

        // Make an HTTP GET request to the specified API endpoint
        $.get(`/api/v1/airports/${icao}/runways`, function (t) {
            // Check if the response has an error status or the code is not 200
            if (t.status == "error" || t.code != 200) {
                // Show an error modal with the received error message
                $("#runway-output").html(ErrorModal(t.message, "runway-modal"));
                $("#runway-modal").modal("show"); // Show the modal with id "runway-modal"
                $("#loading").hide(); // Hide the loading element
                return; // Exit the function
            }

            // TODO: If no runways, show a success message with an override runways button

            // Create a table containing information about runways
            table = `
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><i class="fa-solid fa-road" title="Runway"></i></th>
                            <th scope="col"><i class="fa-solid fa-wind" title="Wind"></i></th>
                            <th scope="col"><i class="fa-solid fa-plus-minus"></i><i class="fa-solid fa-wind" title="Difference"></i></th>
                            <th scope="col"><i class="fa-solid fa-plane-arrival" title="Arrival"></i></th>
                            <th scope="col"><i class="fa-solid fa-plane-departure" title="Departure"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${t.data
                            .map(function (t) {
                                // Generate rows for each runway data
                                return `
                            <tr>
                                <td><strong>${t.runway}</strong></td>
                                <td>${t.wind_dir}</td>
                                <td>${t.wind_diff}</td>
                                <td><input class="form-check-input" type="checkbox" name="landing_runways[]" value="${t.runway}"></td>
                                <td><input class="form-check-input" type="checkbox" name="departing_runways[]" value="${t.runway}"></td>
                            </tr>
                            `;
                            })
                            .join("")}
                    </tbody>
                </table>
                `;

            // Show the generated table in a modal with title and id "runway-modal"
            $("#runway-output").html(
                Modal(
                    "Runway List for " + icao.toUpperCase(),
                    table,
                    "runway-modal"
                )
            );
            $("#runway-modal").modal("show"); // Show the modal with id "runway-modal"
            $("#loading").hide(); // Hide the loading element
        });
    });

    // On form submit event for the element with id "atis-input"
    $("#atis-input").submit(function (t) {
        $("#loading").show(); // Show the loading element
        t.preventDefault(); // Prevent the default form submission
        icao = $("#icao").val(); // Get the value of the input with id "icao"
        ident = $("#ident").val(); // Get the value of the input with id "ident"

        // Make an HTTP POST request to the specified API endpoint with serialized form data
        $.post(
            `/api/v1/airports/${icao}/atis`,
            $("#atis-input").serialize(),
            function (t) {
                // Check if the response has an error status or the code is not 200
                if (t.status == "error" || t.code != 200) {
                    // Show an error modal with the received error message
                    $("#atis-output").html(ErrorModal(t.message, "atis-modal"));
                    $("#atis-modal").modal("show"); // Show the modal with id "atis-modal"
                    $("#loading").hide(); // Hide the loading element
                    return; // Exit the function
                }

                // If the response data is empty
                if (t.data == "") {
                    // Show an error modal with a specific message
                    $("#atis-output").html(
                        ErrorModal("API returned empty data. Please try again.")
                    );
                    $("#atis-modal").modal("show"); // Show the modal with id "atis-modal"
                    $("#loading").hide(); // Hide the loading element
                    return; // Exit the function
                }

                atis = t.data.spoken; // Get the spoken data from the response

                // HTML template for the success modal
                success = `
            <div class="modal fade show" id="atis-modal" tabindex="-1" aria-modal="true" role="dialog" style="display: block;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Success!</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p class="fs-3 text-success"><i class="fa-solid fa-circle-check"></i><br>Generation Success!</p>
                            <p>Your input has been serialized, we've gotten the weather and it's all been run through the processor. It's parsed and your ATIS/AWOS is ready to go! Click on the buttons below to use it.</p>
                            <div id="atis1" class="hide">${t.data.text}</div>
                            <div id="atis2" class="hide">${t.data.spoken}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="copy-atis">Copy To Clipboard</button>
                            <a class="btn btn-primary" id="download-atis" download>Fetching Download Link <i class="fa-solid fa-spinner fa-spin"></i></a>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            `;

                // Show the success modal
                $("#atis-output").html(success);
                $("#atis-modal").modal("show"); // Show the modal with id "atis-modal"
                $("#loading").hide(); // Hide the loading element

                // Make an HTTP POST request to another API endpoint with data for text-to-speech
                tts = $.post(
                    `/api/v1/tts`,
                    { ident: ident, atis: atis, icao: icao },
                    function (t) {
                        // Check if the response has an error status or the code is not 200 or 409
                        if (
                            t.code != 200 &&
                            t.status == "error" &&
                            t.code != 409
                        ) {
                            // Show an error modal with the received error message
                            $("#atis-output").html(
                                ErrorModal(t.message, "atis-modal")
                            );
                            $("#atis-modal").modal("show"); // Show the modal with id "atis-modal"
                            $("#loading").hide(); // Hide the loading element
                            return; // Exit the function
                        }

                        // Show the download button and set its attributes based on the response data
                        $("#download-atis").attr("href", t.data.url);
                        $("#download-atis").html("Download ATIS/AWOS Audio");
                        $("#download-atis").attr("download", t.data.name);
                    }
                );

                // On click event for the element with id "copy-atis"
                $("#copy-atis").click(function () {
                    copy("#atis1"); // Call the 'copy' function to copy the content of the element with id "atis1"
                });
            }
        );
    });

    // On click event for the element with id "squawk-generator"
    $("#squawk-generator").click(function () {
        $("#squawk-modal").modal("show"); // Show the modal with id "squawk-modal"
    });

    /**
     * Generate a random number between 1 and 7
     *
     * @returns {number} Random number
     */
    function getRandomInt() {
        return Math.floor(Math.random() * 7 + 1);
    }

    // On click event for the element with id "generate-squawk"
    $("#generate-squawk").click(function () {
        code = ""; // Initialize an empty string variable called 'code'
        code = code.concat(3); // Concatenate the number 3 to the 'code' variable
        code = code.concat(getRandomInt()); // Concatenate a random integer to 'code'
        code = code.concat(getRandomInt()); // Concatenate another random integer to 'code'
        code = code.concat(getRandomInt()); // Concatenate one more random integer to 'code'
        $("#copy-squawk").show(); // Show the element with id "copy-squawk"
        $("#squawk-output").html(code); // Set the 'code' as the HTML content of the element with id "squawk-output"
    });

    // On click event for the element with id "copy-squawk"
    $("#copy-squawk").click(function () {
        copy("#squawk-output"); // Call the 'copy' function to copy the content of the element with id "squawk-output"
    });

    // On click event for the element with id "metar-generator"
    $("input[type=radio][name=output-type]").change(function () {
        // Check if the value of the selected radio button is "metar"
        if (this.value == "atis") {
            // Show the element with class "atis-hide"
            $(".awos-hide").show();
        }
        // Check if the value of the selected radio button is "awos"
        if (this.value == "awos") {
            // Hide the element with class "atis-hide"
            $(".awos-hide").hide();
        }
    });
});
