$(document).ready(function () {
    $(".collapse-item-royalti").addClass("active");
    $("#collapseStore.collapse").addClass("show");
    $(".nav-item-store .nav-link").removeClass("collapsed");

    var dTableRoyalti = new $("#dTable-royalti").DataTable();

    // pop up waiting alert
    function alertWait(text) {
        // Swal.fire("Loading ...", text, "info");
        Swal.fire({
            title: "Loading ...",
            text: text,
            icon: "info",
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        });
    }

    // pop up success alert
    function alertSuccess(text) {
        Swal.fire({
            title: "Success",
            text: text,
            icon: "success",
            timer: "1500",
        });
    }

    // pop up error alert
    function alertError(error, $timer = null) {
        if ($timer == null) {
            $timer = "2000";
        }
        Swal.fire({
            title: "Error",
            text: error,
            icon: "error",
            timer: $timer,
            confirmButtonColor: "#8CD4F5",
        });
    }

    // pop up info alert
    function alertInfo(info, $timer = null) {
        if ($timer == null) {
            $timer = "2000";
        }
        Swal.fire({
            title: "Info",
            text: info,
            icon: "warning",
            timer: $timer,
            confirmButtonColor: "#8CD4F5",
        });
    }

    // pop up close alert
    function alertWaitClose() {
        $(".swal2-confirm").trigger("click"); // Close swal
    }

    const saveData = (function () {
        const a = document.createElement("a");
        document.body.appendChild(a);
        a.style = "display: none";
        return function (url, fileName) {
            a.href = url;
            a.download = fileName;
            a.click();
        };
    })();

    $("#royalti-date").change(function () {
        let date = $(this).val();
        dTableRoyalti.clear().destroy();
        dTableRoyalti = new $("#dTable-royalti").DataTable({
            ajax: {
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content"),
                },
                url: "get-data-royalti",
                type: "POST",
                data: {
                    date: date,
                },
                dataType: "JSON",
            },
            retrieve: true,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.tko_kodeomi + " - " + row.tko_namaomi;
                    },
                },
                { data: "royalti_date" },
                {
                    data: "tko_kodeomi",
                    render: function (data, type, row, meta) {
                        return (
                            "<button class='btn btn-primary' id='btn-process-royalti' value='" +
                            data +
                            "' data='" +
                            data +
                            "'><i class='fa fa-pen'></i> Process</button> "
                        );
                    },
                },
            ],
        });
    });

    $("#btn-process-all").click(function () {
        if ($("#royalti-date").val() == "") {
            alertInfo("Please select date !");
        } else {
            var score = $(this).val();
            Swal.fire({
                title: "Are you sure?",
                text: "Create File Royalti!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!",
            }).then((result) => {
                console.log("Result: ", result);
                if (result.value) {
                    alertWait("Please Wait !");
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr(
                                "content"
                            ),
                        },
                        type: "POST",
                        url: "create-royalti",
                        data: {
                            data: "",
                            royaltiDate: $("#royalti-date").val(),
                        },
                        dataType: "json",
                        success: function (res) {
                            alertWaitClose();
                            window.open(res.data, "_blank");
                        },
                        error: function (xhr, status, error) {
                            var err = JSON.parse(xhr.responseText);
                            Swal.fire("Info!", err.message, "Danger");
                        },
                    });
                }
            });
        }
    });

    $(document).on("click", "#btn-process-royalti", function () {
        var score = $(this).val();
        Swal.fire({
            title: "Are you sure?",
            text: "Create File Royalti!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes!",
        }).then((result) => {
            if (result.value) {
                alertWait("Please Wait !");
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr(
                            "content"
                        ),
                    },
                    type: "POST",
                    url: "create-royalti",
                    data: {
                        data: $(this).attr("data"),
                        royaltiDate: $("#royalti-date").val(),
                    },
                    dataType: "json",
                    success: function (res) {
                        alertWaitClose();
                        saveData(res.data, res.file_name);
                    },
                    error: function (xhr, status, error) {
                        var err = JSON.parse(xhr.responseText);
                        Swal.fire("Info!", err.message, "Danger");
                    },
                });
            }
        });
    });
});
