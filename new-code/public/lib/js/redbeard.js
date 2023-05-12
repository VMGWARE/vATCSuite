$(document).ready(function () {
    $("input, textarea").on("input", function () {
        var t = this.selectionStart,
            o = /[^0-9a-z\,\.\-\/\s]/gi,
            a = $(this).val();
        if (o.test(a)) {
            $(this).val(a.replace(o, ""));
            t--
        }
        if (this.type !== "checkbox") {
            this.setSelectionRange(t, t)
        }
    });

    /**
     * Copy text to clipboard
     * @param {string} text - The text to copy
     * @returns {void} Nothing
     */
    function copy(text) {
        output = $(text).html();
        navigator.clipboard.writeText(output)
    }

    $("#list-runways").click(function (t) {
        t.preventDefault();
        $(".loading").show();
        icao = $("#icao").val();
        $.get(`/api/v1/airport/${icao}/runways`, function (t) {
            $("#runway-output").html(t);
            $("#runway-modal").modal("show");
            $(".loading").hide()
        })
    });

    $("#atis-input").submit(function (t) {
        $(".loading").show();
        t.preventDefault();
        icao = $("#icao").val();
        $.get(`/api/v1/airport/${icao}/atis`, $("#atis-input").serialize(), function (t) {
            $("#atis-output").html(t);
            $("#atis-modal").modal("show");
            $(".loading").hide();
            atis2 = $("#atis2").html();
            icao = $("#icao").val();
            ident = $("#ident").val();
            $("#download-atis").attr("href", "tts.php?atis=" + atis2 + "&icao=" + icao + "&ident=" + ident);
            $("#copy-atis").click(function () {
                copy("#atis1")
            })
        })
    });

    $("#squawk-generator").click(function () {
        $("#squawk-modal").modal("show")
    });

    /**
     * Generate a random number between 1 and 7
     *
     * @returns {number} Random number
     */
    function getRandomInt() {
        return Math.floor(Math.random() * 7 + 1)
    }

    $("#generate-squawk").click(function () {
        code = "";
        code = code.concat(3);
        code = code.concat(getRandomInt());
        code = code.concat(getRandomInt());
        code = code.concat(getRandomInt());
        $("#copy-squawk").show();
        $("#squawk-output").html(code)
    });

    $("#copy-squawk").click(function () {
        copy("#squawk-output")
    })
});