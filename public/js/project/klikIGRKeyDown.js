let tb, tb_edit_pb, tb_hitung_ulang;
let countPasswordManager = 0;

$('#modal_detail').on('shown.bs.modal', function() {
    tb.columns.adjust()
})
$('#modal_edit_pb').on('shown.bs.modal', function() {
    tb_edit_pb.columns.adjust()
})
$('#modal_hitung_ulang').on('shown.bs.modal', function() {
    tb_hitung_ulang.columns.adjust()
})

function initialize_datatables_detail(data, columnsData, columnsDefsData = []){
    columnsDefsData.push({ className: 'text-center', targets: "_all" });
    tb_detail = $('#tb_detail').DataTable({
        data: data,
        language: {
            emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
        },
        columnDefs: columnsDefsData,
        columns: columnsData,
        ordering: false,
        pageLength: 5,
        lengthChange: false,
        destory: true,
    });
}

function draw_tb_edit_pb(selectedRow){
    tb_edit_pb.clear().draw();
    $('.datatable-no-data').css('color', '#F2F2F2');
    $('#loading_datatable_edit_pb').removeClass('d-none');
    $.ajax({
        url: currentURL + "/action/f4",
        type: "POST",
        data: { actionSelected: $("#action_form_pembatalan").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
        success: function(response) {
            $('#loading_datatable_edit_pb').addClass('d-none');
            $('.datatable-no-data').css('color', '#ababab');
            tb_edit_pb.rows.add(response.data).draw();
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#loading_datatable_edit_pb').addClass('d-none'); }, 500);
            $('.datatable-no-data').css('color', '#ababab');
            Swal.fire({
                text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

// START GLOBAL FUNCTION
function isModalShowing() {
    const modals = $('.modal'); 
    for (let modal of modals) {
        if ($(modal).hasClass('show')) {
            return true; 
        }
    }
    return false; 
}

function showModalPasswordManager(status, mode = "isManager"){
    var modalElement = $('#modal_password_manager');
    modalElement.modal("show");
    modalElement.attr("status", status);
    modalElement.attr("mode", mode);
    $("#password_manager").val('');
    if(status === 'edit_pb'){
        $("#modal_edit_pb").addClass("brightness-blur");
    }
}

function closeModalPasswordManager(){
    var modalElement = $('#modal_password_manager');
    modalElement.modal('hide'); 
    $("#password_manager").val('');
    if(modalElement.attr('status') === 'edit_pb'){
        $("#modal_edit_pb").removeClass("brightness-blur");
    }
    modalElement.attr("status", "");    
    modalElement.attr("mode", "");    
}

function showValidasiStruk(){
    var selectedRow = tb.row(".select-r").data();
    $("#modal_validasi_struk").modal("show");
    $("#no_trans_validasi_struk").val(selectedRow.no_trans);
    $("#no_pb_validasi_struk").val(selectedRow.no_pb);
    $("#tanggal_trans_validasi_struk").val(moment(selectedRow.tgltrans, 'DD-MM-YYYY').format('YYYY-MM-DD'));
    $("#member_validasi_struk").val(selectedRow.kode_member);

    //DETAIL
    setDateNow("#tanggal_struk_validasi_struk");
    setTimeNow("#time_struk_validasi_struk");
}

function closeValidasiStruk(){
    $("#modal_validasi_struk").modal("hide");
    $("#no_trans_validasi_struk").val("");
    $("#no_pb_validasi_struk").val("");
    $("#tanggal_trans_validasi_struk").val("");
    $("#member_validasi_struk").val("");

    //DETAIL
    $("#no_struk_validasi_struk").val("");
    $("#tanggal_struk_validasi_struk").val("");
    $("#time_struk_validasi_struk").val("");
    $("#station_validasi_struk").val("");
    $("#cashier_id_validasi_struk").val("");
}

function toggleModalHitungUlang(no_pb = "", tanggal_pb = "", kode_member = "", nama_member = "") {
    $("#modal_hitung_ulang").modal("toggle");
    $("#no_pb_hitung_ulang").text(no_pb);
    $("#tanggal_pb_hitung_ulang").text(moment(tanggal_pb, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'));
    $("#kode_member_hitung_ulang").text(kode_member);
    $("#nama_member_hitung_ulang").text(nama_member);
}

function getDataHitungUlang() {
    var data = tb_hitung_ulang.rows().data().toArray();

    $('.input-hitung-ulang').each(function(index) {
        var inputValue = parseFloat($(this).val()); 
        data[index].qtyinput = inputValue; 
        data[index].qtypb = isNaN(inputValue) ? 0 : parseFloat(parseInt(inputValue)).toFixed(0);
    });

    return data;
}

// END GLOBAL FUNCTION

// START ADDITIONAL FUNCTION
function actionAdditionalValidasiRak(rowsData){
    rowsData = rowsData.map(function(row) {
        return { plu: row.plu };
    });

    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f4-validasi-rak`,
        type: "POST",
        data: {datatables: rowsData, no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire('Success!', response.message,'success');
            var selectedRow = tb.row(".select-r").data();
            draw_tb_edit_pb(selectedRow);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalItemBatal(rowsData, directCetak = false){
    $('#modal_loading').modal('show');
    var url_action = directCetak ? "/action/f4-cetak-item-batal" : "/action/f4-item-batal";
    $.ajax({
        url: currentURL + url_action,
        type: "POST",
        data: {datatables: rowsData, no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb,},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            var swalMessage = directCetak ? "Cetak Item Batal Berhasil" : response.message;
            Swal.fire('Success!', swalMessage,'success');
            var blob = new Blob([response.data.content], { type: "text/plain" });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = response.data.nama_file;
            link.click();
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalPasswordManager(){
    var password_manager = $("#password_manager").val();
    if(password_manager === "" || password_manager === undefined){
        Swal.fire('Peringatan!', 'Harap isi Password Terlebih Dahulu..!', 'warning');
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/password-manager`,
        type: "POST",
        data: { password_manager: $("#password_manager").val(), count: countPasswordManager, mode: $("#modal_password_manager").attr("mode") },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire('Success!', response.message,'success').then(function(){
                var status = $("#modal_password_manager").attr('status');
                if(status === 'edit_pb'){
                    actionF4Proses(true);
                } else if(status === 'reaktivasi_pb'){
                    action_f5(true);
                } else if(status === 'validasi_struk'){
                    showValidasiStruk();
                } else if(status === 'pembatalan_dsp'){
                    action_f12(true);
                } else if(status === 'batalin_pb'){
                    action_delete(true);
                }
                closeModalPasswordManager();
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            countPasswordManager += 1;
            if(countPasswordManager >= 3){
                countPasswordManager = 0;
                Swal.fire('Peringatan!', jqXHR.responseJSON.message, 'warning').then(function(){
                    closeModalPasswordManager();
                });
                return;
            }
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });


}

function actionAdditionalHitungUlang(){
    var datatables = getDataHitungUlang();
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f10-hitung-ulang`,
        type: "POST",
        data: { datatables: datatables, tanggal_trans: $("#tanggal_trans").val(), selectedRow: selectedRow },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire('Success!', response.message,'success');
            var blob = new Blob([response.data.str], { type: "text/plain" });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = response.data.nama_file;
            link.click();
            toggleModalHitungUlang();
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalSimpanPembatalanPB(){

    var selectedRow = tb.row(".select-r").data();
    var alasanValue = $(".select-pembatalan-pb").val();
    var lainValue = $(".input-pembatalan-pb").val();
    if(alasanValue == "lain-lain" && (lainValue == '' || lainValue == '-')){
        Swal.fire('Peringatan!', 'Alasan Lain-lain belum diinput','warning');
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/delete`,
        type: "POST",
        data: { alasanValue: alasanValue, lainValue: lainValue, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, tanggal_trans:  selectedRow.tgltrans},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire('Success!', response.message,'success').then(function(){
                $("#modal_pembatalan_pb").modal("hide");
                $("#tb_pembatalan_pb tbody").empty();
            });
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}
// END ADDITIONAL FUNCTION

function action_f1(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/f1",
        type: "POST",
        data: {no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb},
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            $("#modal_detail").modal("show");
            $(".detail-title").text("DETAIL PB");
            
            if ($.fn.DataTable.isDataTable('#tb_detail')) {
                tb_detail.clear().draw();
                $("#tb_detail").dataTable().fnDestroy();
                $("#tb_detail thead").empty()
            }

            var newColumns = [
                { data: 'plu', title: 'PLU' },
                { data: 'barang', title: 'Barang' },
                { data: 'jumlah', title: 'Jumlah' },
                { data: 'harga', title: 'Harga' },
                { data: 'diskon', title: 'Diskon' },
                { data: 'subtotal', title: 'Subtotal' },
                { data: 'tag', title: 'Tag' }
            ];

            response.data = response.data.map(function(item){
                if(item.jumlah !== null){
                    item.jumlah = parseFloat(item.jumlah).toFixed(0);
                }
                return item;
            });

            initialize_datatables_detail(response.data, newColumns);
            
            if(response.data.length > 0){
                window.open(currentURL + "/action/f1-download-excel", '_blank');
            }

        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f2(){
    $('#modal_loading').modal('show');
    var selectedRow = tb.row(".select-r").data();
    $.ajax({
        url: currentURL + `/action/f2`,
        type: "POST",
        data: {member_igr: selectedRow.kode_member, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            $("#modal_detail").modal("show");
            $(".detail-title").text("DETAIL PROMO");

            if ($.fn.DataTable.isDataTable('#tb_detail')) {
                tb_detail.clear().draw();
                $("#tb_detail").dataTable().fnDestroy();
                $("#tb_detail thead").empty()
            }

            var newColumns = [
                { data: 'KODE PROMO', title: 'KODE PROMO' },
                { data: 'potongan', title: 'POTONGAN' },
                { data: 'promo', title: 'PROMO' },
                { data: 'tipe', title: 'TIPE' },
            ];

            initialize_datatables_detail(response.data, newColumns);

        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f3(){
    $('#modal_loading').modal('show');
    var selectedRow = tb.row(".select-r").data();
    $.ajax({
        url: currentURL + `/action/f3`,
        type: "POST",
        data: {no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            $("#modal_detail").modal("show");
            $(".detail-title").text("DETAIL OTV PICKING");

            if ($.fn.DataTable.isDataTable('#tb_detail')) {
                tb_detail.clear().draw();
                $("#tb_detail").dataTable().fnDestroy();
                $("#tb_detail thead").empty()
            }

            response.data = response.data.map(item => ({
                ...item,
                qty_order: item.qty_order !== null ? parseFloat(item.qty_order).toFixed(0) : item.qty_order,
                qty_picking: item.qty_picking !== null ? parseFloat(item.qty_picking).toFixed(0) : item.qty_picking,
                qty_packing: item.qty_packing !== null ? parseFloat(item.qty_packing).toFixed(0) : item.qty_packing
            }));

            var newColumns = [
                { data: 'plu', title: 'PLU' },
                { data: 'deskripsi', title: 'DESKRIPSI' },
                { data: 'qty_order', title: 'QTY_ORDER' },
                { data: 'qty_picking', title: 'QTY_PICKING' },
                { data: 'status_picking', title: 'STATUS_PICKING' },
                { data: 'group_name', title: 'GROUP' },
                { data: 'picker', title: 'PICKER' },
                { data: 'qty_packing', title: 'QTY_PACKING' },
            ];

            var newColumnDefs = [{ className: 'w-40-center', targets: 1 }];

            initialize_datatables_detail(response.data, newColumns, newColumnDefs);

        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f4(){
    Swal.fire({
        title: 'Yakin?',
        html: `Edit PB/Validasi Rak untuk Item Batal?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            var selectedRow = tb.row(".select-r").data();
            $("#modal_edit_pb").modal("show");
            $("#no_pb_detail_edit").text(selectedRow.no_pb);
            $("#tanggal_pb_detail_edit").text(selectedRow.tgl_pb);
            $("#no_trans_detail_edit").text(selectedRow.no_trans);
            if (["Siap Picking", "Set Ongkir", "Siap Draft Struk", statusSiapPacking].includes(selectedRow.status)) {
                $("#action_form_pembatalan").append(`<option value="ITEM BATAL">Item Batal</option>`);
            }
            draw_tb_edit_pb(selectedRow);
        }
    });
}

function actionF4Proses(passPasswordManager = false){
    var swalText, functionName, checkedRowsData = [];
    var isChecked = false;

    $(".checkbox-group").each(function() {
        if ($(this).prop('checked')) {
            checkedRowsData.push(tb_edit_pb.row($(this).closest('tr')).data())
            isChecked = true;
        }
    });
    
    if(isChecked === false){
        Swal.fire('Peringatan!', 'Item Belum Dipilih..!', 'warning');
        return;
    }

    if($("#action_form_pembatalan").val() === "VALIDASI RAK"){
        swalText = `Validasi ${$(".checkbox-group:checked").length} Item yang sudah dikembalikan ke rak ?`;
        functionName = "actionAdditionalValidasiRak";
    } else {
        if(passPasswordManager === false){
            showModalPasswordManager('edit_pb', 'isManager');
            return;
        }
        swalText = `Proses ${$(".checkbox-group:checked").length} Item Batal ?`;
        functionName = "actionAdditionalItemBatal";
    }

    Swal.fire({
        title: 'Yakin?',
        html: swalText,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            if (typeof window[functionName] === 'function') {
                window[functionName](checkedRowsData);
            }
        }
    });
}

function action_f5(passPasswordManager = false){
    var selectedRow = tb.row(".select-r").data();
    if(passPasswordManager === false){
        if(selectedRow.status === "Transaksi Batal" && selectedRow.flagbayar !== "Y"){
            Swal.fire({
                title: 'Yakin?',
                html: `Mengaktifkan Kembali Transaksi No.${selectedRow.no_trans} yang sudah batal?`,
                icon: 'info',
                showCancelButton: true,
            })
            .then((result) => {
                if (result.value) {
                    showModalPasswordManager('reaktivasi_pb', 'isManager');
                }
            });
        } else {
            Swal.fire('Peringatan!', 'Bukan data yang bisa diaktifkan kembali..!', 'warning');
            return;
        }
    } else {
        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/action/f5`,
            type: "POST",
            data: { status: selectedRow.status, flag_bayar: selectedRow.flagbayar, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, tanggal_trans: $("#tanggal_trans").val() },
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success');
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }
}

function action_f6(passPasswordManager = false){
    var selectedRow = tb.row(".select-r").data();
    if(passPasswordManager === false){
        if(selectedRow.status === "Siap Struk"){
            showModalPasswordManager('validasi_struk', 'isOTP');
        } else {
            Swal.fire('Peringatan!', 'Bukan data yang bisa validasi struk..!', 'warning');
            return;
        }
    } else {
        var no_struk = $("#no_struk_validasi_struk").val(),
            tanggal_struk = $("#tanggal_struk_validasi_struk").val(),
            time_struk = $("#time_struk_validasi_struk").val(),
            station = $("#station_validasi_struk").val(),
            cashier = $("#cashier_id_validasi_struk").val();

        if (!no_struk || !tanggal_struk || !time_struk || !station || !cashier) {
            Swal.fire('Peringatan!', 'Input Detail Struk Belum Lengkap..!', 'warning');
            return;
        }

        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/action/f6`,
            type: "POST",
            data: { no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member, tanggal_trans: selectedRow.tgltrans, no_struk: no_struk, tanggal_struk: tanggal_struk, time_struk: time_struk, station: station, cashier: cashier, status: selectedRow.status },
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success').then(function(){
                    tb.ajax.reload();
                    closeValidasiStruk();
                });
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }
}

function action_f7(){
    var selectedRow = tb.row(".select-r").data();s
    if(selectedRow.status !== "Siap Picking" || selectedRow.status !== statusSiapPacking){
        Swal.fire('Peringatan!', 'Bukan data yang dipicking/dipacking..!', 'warning');
        return;
    }
    var dataRequest = { status: selectedRow.status, status_siap_packing: statusSiapPacking, tanggal_trans: $("#tanggal_trans").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f7`,
        type: "POST",
        data: dataRequest,
        xhrFields: {
            responseType: 'blob' // Important for binary data
        },
        success: function(response) {
            $.ajax({
                url: currentURL + `/action/f7`,
                type: "POST",
                data: dataRequest,
                xhrFields: {
                    responseType: 'blob' // Important for binary data
                },
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    var blob = new Blob([response], { type: 'application/pdf' }); // Corrected line
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'RPT-JALUR-KERTAS.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            })
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f8(){
    var selectedRow = tb.row(".select-r").data();
    var dataRequest = { tanggal_trans:  $("#tanggal_trans").val()}
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f8`,
        type: "POST",
        data: dataRequest,
        success: function(response) {
            $.ajax({
                url: currentURL + `/action/f8`,
                type: "POST",
                data: dataRequest,
                xhrFields: {
                    responseType: 'blob' // Important for binary data
                },
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    var blob = new Blob([response], { type: 'application/pdf' }); // Corrected line
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'RPT-PENYUSUTAN-HARIAN.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            })
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f9(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f9`,
        type: "POST",
        data: {status: selectedRow.status, status_siap_packing: statusSiapPacking, tanggal_trans: $("#tanggal_trans").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                title: 'Yakin?',
                html: response.message,
                icon: 'info',
                showCancelButton: true,
            })
            .then((result) => {
                if (result.value) {
                    var blob = new Blob([response.data.content], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = response.data.nama_file;
                    link.click();
                }
            });
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f10(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f10`,
        type: "POST",
        data: {status: selectedRow.status, tipe_bayar: selectedRow.tipe_bayar, tanggal_trans: $("#tanggal_trans").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, tanggal_pb: selectedRow.tgl_pb, kode_member: selectedRow.kode_member, tipe_kredit: selectedRow.tipe_kredit },
        success: function(response) {
            var request = response.data.request;
            var data = response.data.data.original.data;
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            toggleModalHitungUlang(request.nopb, request.tanggal_pb, request.kode_member, request.nama_member);
            tb_hitung_ulang.clear().draw();
            tb_hitung_ulang.rows.add(data).draw();
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_f12(passPasswordManager = false){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/f12`,
        type: "POST",
        data: { status: selectedRow.status, tanggal_pb: selectedRow.tgl_pb, status_siap_packing: statusSiapPacking, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member, tanggal_trans: $("#tanggal_trans").val(), pass_password_manager: passPasswordManager },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            if(response.code === 201){
                Swal.fire({
                    title: 'Yakin?',
                    html: response.message,
                    icon: 'info',
                    showCancelButton: true,
                })
                .then((result) => {
                    if (result.value) {
                        showModalPasswordManager('pembatalan_dsp', response.data);
                    }
                    return;
                });
            } else {
                Swal.fire('Success!', response.message,'success');
            }
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function action_delete(passPasswordManager = false){
    var selectedRow = tb.row(".select-r").data();
    var dgv_status = selectedRow.status;
    if (dgv_status == statusSiapPicking || 
    dgv_status == "Siap Picking" || 
    dgv_status == statusSiapPacking || 
    dgv_status == "Set Ongkir" || 
    dgv_status == "Siap Draft Struk" || 
    dgv_status == "Konfirmasi Pembayaran" || 
    dgv_status == "Siap Struk") {
        if(passPasswordManager === true){
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/delete-alasan-pembatalan-pb`,
                type: "GET",
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    $("#tb_pembatalan_pb tbody").empty();
                    var data = response.data;
                    var cmb = $("<select>").addClass("form-control select-pembatalan-pb");
                    $.each(data, function(index, item) {
                        if (item.no === "1") {
                            cmb.append($("<option>").attr("value", item.alasan).text(item.alasan).prop("selected", true));
                        } else {
                            cmb.append($("<option>").attr("value", item.alasan).text(item.alasan));
                        }
                    });
                    cmb.append($("<option>").attr("value", "lain-lain").text("Lain-Lain"));

                    var cmbCell = $("<td>").append(cmb);
                    var cmtCell = $("<td>").append($("<input>").addClass("form-control input-pembatalan-pb").attr({type: "text", name: "colLain", maxlength: "50", disabled: true}).val("-"));

                    var newRow = $("<tr>").append(
                        $("<td>").text(selectedRow.no_pb),
                        $("<td>").text(selectedRow.no_trans),
                        $("<td>").text(moment(selectedRow.tgl_pb).format("DD-MM-YYYY")),
                        cmbCell,
                        cmtCell
                    );

                    $("#tb_pembatalan_pb").append(newRow);

                    cmb.on("change", function() {
                        if ($(this).val() === "lain-lain") {
                            $(".input-pembatalan-pb").prop("disabled", false).val("");
                        } else {
                            $(".input-pembatalan-pb").prop("disabled", true).val("-");
                        }
                    });

                    $("#modal_pembatalan_pb").modal("show");
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                            ? jqXHR.responseJSON.message
                            : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            });
            return;
        }
        Swal.fire({
            title: 'Yakin?',
            html: "Anda yakin akan membatalkan Transaksi No." & selectedRow.no_trans & " ?",
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                showModalPasswordManager('batalin_pb', "isManager");
            }
        });
    } else{
        Swal.fire('Peringatan!', 'Bukan data yang bisa dibatalkan!', 'warning');
        return;
    }
}