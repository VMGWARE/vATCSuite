$(document).ready(function () {
    $("input, textarea").on("input", function () {
        var t = this.selectionStart,
            o = /[^0-9a-z\,\.\-\/\s]/gi,
            a = $(this).val();
        if (o.test(a)) {
            $(this).val(a.replace(o, ""));
            t--;
        }
        if (this.type !== "checkbox") {
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

    $("#list-runways").click(function (t) {
        t.preventDefault();
        $(".loading").show();
        icao = $("#icao").val();
        $.get(`/api/v1/airports/${icao}/runways`, function (t) {
            if (t.status == "error" || t.code != 200) {
                $("#runway-output").html(ErrorModal(t.message, "runway-modal"));
                $("#runway-modal").modal("show");
                $(".loading").hide();
                return;
            }

            // TODO: If not runways show success with overide runways button

            table = `
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><i class="fa-solid fa-road"></i></th>
                        <th scope="col"><i class="fa-solid fa-wind"></i></th>
                        <th scope="col"><i class="fa-solid fa-plus-minus"></i><i class="fa-solid fa-wind"></i></th>
                        <th scope="col"><i class="fa-solid fa-plane-arrival"></i></th>
                        <th scope="col"><i class="fa-solid fa-plane-departure"></i></th>
                    </tr>
                </thead>
                <tbody>
                    ${t.data
                        .map(function (t) {
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
            $("#runway-output").html(
                Modal(
                    "Runway List for " + icao.toUpperCase(),
                    table,
                    "runway-modal"
                )
            );
            $("#runway-modal").modal("show");
            $(".loading").hide();
        });
    });

    $("#atis-input").submit(function (t) {
        $(".loading").show();
        t.preventDefault();
        icao = $("#icao").val();
        ident = $("#ident").val();
        $.post(
            `/api/v1/airports/${icao}/atis`,
            $("#atis-input").serialize(),
            function (t) {
                if (t.status == "error" || t.code != 200) {
                    $("#atis-output").html(ErrorModal(t.message, "atis-modal"));
                    $("#atis-modal").modal("show");
                    $(".loading").hide();
                    return;
                }

                if (t.data == "") {
                    $("#atis-output").html(
                        ErrorModal("API returned empty data. Please try again.")
                    );
                    $("#atis-modal").modal("show");
                    $(".loading").hide();
                    return;
                }

                atis = t.data.spoken;

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
                            <p>Your input has been serialized, we've gotten the weather and it's all been run through the processor. It's parsed and your ATIS ready to go! Click on the buttons below to use it.</p>
                            <div id="atis1" class="hide">${t.data.text}</div>
                            <div id="atis2" class="hide">${t.data.spoken}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="copy-atis">Copy ATIS To Clipboard</button>
                            <a class="btn btn-primary" id="download-atis" download>Fetching Download Link <i class="fa-solid fa-spinner fa-spin"></i></a>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            `;

                // Show success modal
                $("#atis-output").html(success);
                $("#atis-modal").modal("show");
                $(".loading").hide();

                tts = $.post(
                    `/api/v1/tts`,
                    { ident: ident, atis: atis, icao: icao },
                    function (t) {
                        if (
                            t.code != 200 &&
                            t.status == "error" &&
                            t.code != 409
                        ) {
                            $("#atis-output").html(
                                ErrorModal(t.message, "atis-modal")
                            );
                            $("#atis-modal").modal("show");
                            $(".loading").hide();
                            return;
                        }

                        // Show download button
                        $("#download-atis").attr("href", t.data.url);
                        $("#download-atis").html("Download ATIS");
                        $("#download-atis").attr("download", t.data.name);
                    }
                );

                $("#copy-atis").click(function () {
                    copy("#atis1");
                });
            }
        );
    });

    $("#squawk-generator").click(function () {
        $("#squawk-modal").modal("show");
    });

    /**
     * Generate a random number between 1 and 7
     *
     * @returns {number} Random number
     */
    function getRandomInt() {
        return Math.floor(Math.random() * 7 + 1);
    }

    $("#generate-squawk").click(function () {
        code = "";
        code = code.concat(3);
        code = code.concat(getRandomInt());
        code = code.concat(getRandomInt());
        code = code.concat(getRandomInt());
        $("#copy-squawk").show();
        $("#squawk-output").html(code);
    });

    $("#copy-squawk").click(function () {
        copy("#squawk-output");
    });
});
