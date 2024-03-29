$(document).ready(function () {
    console.log("login");
    // SweatAlert
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

    $(".form-login").submit(function (e) {
        e.preventDefault();
        var validation = true;
        $(".alert").remove();

        $('input[id^="txt-"]').each(function () {
            if (!$(this).val()) {
                $(this).addClass("alert-danger");
                validation = false;
            } else {
                $(this).removeClass("alert-danger");
            }
        });

        if (validation) {
            $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                type: "post",
                url: "login",
                data: {
                    username: $('input[name="username"]').val(),
                    password: $('input[name="password"]').val(),
                    branch: $("#select-branch").val(),
                    branchname: $("#select-branch + button").attr("title"),
                    type: $("#select-server").val(),
                },
                dataType: "JSON",
                success: function (response) {
                    window.location.replace("home");
                },
                error: function (xhr, status, error) {
                    var err = JSON.parse(xhr.responseText);
                    $(".alert").remove();
                    $(".alert").remove();
                    var html = "";
                    html +=
                        "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
                    html += "<strong>" + err.error + "</strong>";
                    html += "</div>";
                    $(".card-alert").html(html).fadeIn();
                    $(".card-alert").delay("5000").fadeOut();
                },
            });
        }
    });
});
