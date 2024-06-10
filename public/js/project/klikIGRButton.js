let tb_periode_pesanan, tb_ba_rusak;

function initialize_datatables_list_pb(data, columnsData, columnsDefsData = []){
    columnsDefsData.push({ className: 'text-center', targets: "_all" });
    tb_periode_pesanan = $('#modal_list_pb_batal_tb').DataTable({
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

function initialize_datatables_master_data(data, columnsData, columnsDefsData = []){
    columnsDefsData.push({ className: 'text-center', targets: "_all" });
    modal_master_data_tb = $('#modal_master_data_tb').DataTable({
        data: data,
        language: {
            emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
        },
        order: [],
        "paging": false,
        "searching": false,
        "scrollY": "calc(100vh - 500px)",
        "scrollCollapse": true,
        columnDefs: columnsDefsData,
        columns: columnsData,
        ordering: false,
        destory: true,
        rowCallback: function(row, data){
            $(row).click(function() {
                $('#modal_master_data_tb tbody tr').removeClass('select-r');
                $(this).toggleClass("select-r");
            });
        },
    });
}

function getCheckedBAPengembalianDana(){
    var checkedInputs = [];
    $('#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table:checked').each(function() {
        var row = $(this).closest('tr');
        var rowData = {
            tipeBayar: row.find('td:eq(0)').text(),
            noPB: row.find('td:eq(1)').text(),
            tglPB: row.find('td:eq(2)').text(),
            kodeMember: row.find('td:eq(3)').text(),
            nilaiRefund: row.find('td:eq(4)').text()
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getCheckedListingDelivery(){
    var checkedInputs = [];
    $('#modal_listing_delivery_tb tbody tr td input.checkbox-table:checked').each(function() {
        var row = $(this).closest('tr');    
        var rowData = {
            tipeBayar: row.find('td:eq(0)').text(),
            noPB: row.find('td:eq(1)').text(),
            tglPB: row.find('td:eq(2)').text(),
            kodeMember: row.find('td:eq(3)').text(),
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getCheckedMasterPicking(){
    var checkedInputs = [];
    $('#modal_master_picking_tb1 tbody tr td input.checkbox-table:checked').each(function() {
        var row = $(this).closest('tr');    
        var rowData = {
            koderak: row.find('td:eq(0)').text(),
            kodesubrak: row.find('td:eq(1)').text(),
            pick: row.find('td:eq(2)').text(),
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getSelectedMasterPicking2(){
    var selectedInputs = [];
    $('#modal_master_picking_tb2 tbody tr.select-r').each(function() {
        var row = $(this);    
        var rowData = {
            urutan: row.find('td:eq(0)').text(),
            koderak: row.find('td:eq(1)').text(),
            kodesubrak: row.find('td:eq(2)').text(),
        };
        selectedInputs.push(rowData);
    });
    return selectedInputs;
}

function getCheckedMasterPickingGroup(){
    var checkedInputs = [];
    $('#modal_master_picking_group_tb1 tbody tr td input.checkbox-table:checked').each(function() {
        var row = $(this).closest('tr');    
        var rowData = {
            id: row.find('td:eq(0)').text(),
            nama: row.find('td:eq(1)').text(),
            pick: row.find('td:eq(2)').text(),
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getCheckedReCreateAWB(){
    var checkedInputs = [];
    $('#modal_re_create_awb_tb tbody tr.select-r').each(function() {
        var row = $(this);
        var rowData = {
            kdmember: row.find('td:eq(0)').text(),
            notrans: row.find('td:eq(1)').text(),
            tgltrans: row.find('td:eq(2)').text(),
            nopb: row.find('td:eq(3)').text(),
            alasan: row.find('td:eq(4)').text(),
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getCheckedMasterPickingGroup2(){
    var selectedInputs = [];
    $('#modal_master_picking_group_tb2 tbody tr.select-r').each(function() {
        var row = $(this);    
        var rowData = {
            grup: row.find('td:eq(0)').text(),
            id: row.find('td:eq(1)').text(),
            nama: row.find('td:eq(2)').text(),
        };
        selectedInputs.push(rowData);
    });
    return selectedInputs;
}

function getCheckedSerahTerimaKardus(){
    var checkedInputs = [];
    $('#tb_bukti_stk tbody tr td input.checkbox-table:checked').each(function() {
        var row = $(this).closest('tr');
        var rowData = {
            noPB: row.find('td:eq(0)').text(),
            tglPB: row.find('td:eq(1)').text(),
            kodeMember: row.find('td:eq(2)').text(),
        };
        checkedInputs.push(rowData);
    });
    return checkedInputs;
}

function getListRakMasterPicking(){
    var formattedData = [];
    if ($.fn.DataTable.isDataTable('#modal_master_picking_tb2')) {
        var data = modal_master_picking_tb2.data().toArray();
        formattedData = data.map(item => `${item.koderak}|${item.kodesubrak}`);
    }
    return formattedData;
}

function actionAdditionalPesananExpired(){
    var date_awal = $("#date_awal_modal_periode_pesanan").val();
    var date_akhir = $("#date_akhir_modal_periode_pesanan").val();
    if(date_awal == '' || date_akhir == ''){
        Swal.fire("Peringatan", "Harap Pilih Priode Terlebih Dahulu", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/LaporanPesananExpired`,
        type: "POST",
        data: {periodeAwal: date_awal, periodeAkhir: date_akhir},
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            var blob = new Blob([response], { type: 'application/pdf' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'LAPORAN PESANAN EXPIRED.pdf';
            link.click();
            $("#modal_periode_pesanan").modal("hide");
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


function actionAdditionalHitungUlangEkspedisi(){
    if($("#txt_nama_modal_ekspedisi").val() == ''){
        Swal.fire("Peringatan", "Nama Ekspedisi Belum Diinput!", "warning");
        return;
    }
    var kg_berat = $("#kg_berat_modal_ekspedisi").val();
    if (kg_berat == "") {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Jarak Pengiriman Belum Anda Input.',
        });
        $("#kg_berat_modal_ekspedisi").focus();
        return false;
    } else if (parseFloat(kg_berat) == 0) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Jarak Pengiriman Belum Anda Input.',
        });
        $("#kg_berat_modal_ekspedisi").focus();
        return false;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/HitungUlang`,
        type: "POST",
        data: {pengiriman: $("#pengiriman_modal_ekspedisi").val(), txtNama: $("txt_nama_modal_ekspedisi").val(), jarak: $("#jarak_modal_ekspedisi").val()},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            
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

function actionAdditionalPembayaranVaChangeButton(){
    $("#btn_proses_modal_pembayaran_va").addClass("d-none");
    $("#btn_refresh_modal_pembayaran_va").removeClass("d-none");
}

function actionAdditionalCekPaymentChangeStatus(){
    var no_trx = $("#no_pb_modal_pembayaran_va").val().substring(0, 6);
    var data = "trxid=" + no_trx + "";
    //! BELUM SELESAI (UNAUTHORIZED)
    // (async () => {
    //     var dataValue = await connectToWebService($("#btn_refresh_modal_pembayaran_va").attr("urlCekPaymentChangeStatus"), "POST", data);
    //     if (dataValue) {
    //         console.log(dataValue);
    //         $("#status_modal_pembayaran_va")
    //     }
    // })();
}

function actionAdditionalLoppCodCetak(){
    if($("#modal_sorting_lopp_sortby1").val() == "" || $("#modal_sorting_lopp_sortby2").val() == ""){
        Swal.fire("Peringatan!", "Harap Pilih Tipe SortBy Terlebih Dahulu!");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/LoppCod`,
        type: "POST",
        data: { sortBy1: $("#modal_sorting_lopp_sortby1").val(), sortBy2: $("#modal_sorting_lopp_sortby2").val() },
        success: function(response) {
            actionGlobalDownloadPdf(response.data.nama_file);
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

function actionAdditionalCreatePaymentChange(){
    var trxid = $("#no_pb_modal_pembayaran_va").val().substring(0, 6);
    var amount = $("#ammount_modal_pembayaran_va").text().replace(/,/g, '').trim();
    var idpayment = $("#bank_modal_pembayaran_va").val();
    if(idpayment == ''){
        Swal.fire("Peringatan !", "Mohon Pilih Bank Terlebih Dahulu.", "warning");
        return;
    }

    //! BELUM SELESAI (MALAH RETURN TAMPILAN APAKAH HARUS REDIRECT ??)
    (async () => {
        var data = "trxid=" + trxid + "&amount=" + amount + "&idpayment=" + idpayment;
        var dataValue = await connectToWebService($("#btn_proses_modal_pembayaran_va").attr("urlCreatePaymentChange"), "POST", data);
        if (dataValue) {

        }
    })();
}

function actionAdditionalQueryDatatablesMasterData(flagMode = ""){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterAlasanBatalKirimDatatables/" + flagMode,
        type: "GET",
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            
            if ($.fn.DataTable.isDataTable('#modal_master_data_tb')) {
                modal_master_data_tb.clear().draw();
                $("#modal_master_data_tb").dataTable().fnDestroy();
                $("#modal_master_data_tb thead").empty()
            }

            var firstObject = response.data[0];

            var newColumns = [];

            for (var property in firstObject) {
                var firstChar = property.toUpperCase().replace(/_/g, " ");

                newColumns.push({
                    data: property,
                    title: firstChar,
                });
            }

            initialize_datatables_master_data(response.data, newColumns);

        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalShowModalMasterData(title = "", labelText = "", flagMode = ""){
    $("#modal_master_data").modal("show");
    $("#modal_master_data_title").text(title);
    $("#modal_master_data_label").text(labelText);
    $("#modal_master_data_flag_mode").val(flagMode);
    actionAdditionalQueryDatatablesMasterData(flagMode);
}

function actionAdditionalAddMasterData(){
    var inputValue = $("#modal_master_data_input").val();
    if(inputValue == ""){
        Swal.fire("Peringatan!", "Harap isi No.Polisi terlebih dahulu");
        return;
    }
    Swal.fire({
        title: 'Yakin?',
        html: `Input No.Polisi ${inputValue} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + "/action/actionMasterAlasanBatalKirimAdd/",
                type: "POST",
                data: { data: inputValue },
                success: function(response) {
                    setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                    Swal.fire("Success!", response.message, "success");
                    actionAdditionalQueryDatatablesMasterData($("#modal_master_data_flag_mode").val());
                }, error: function(jqXHR, textStatus, errorThrown) {
                    setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                            ? jqXHR.responseJSON.message
                            : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            });
        }
    });
}

function actionAdditionalBAPengembalianDanaDatatables(noba, isHistory){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionBAPengembalianDanaDatatables/" + noba + "/" + isHistory,
        type: "GET",
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            if ($.fn.DataTable.isDataTable('#modal_ba_pengembalian_dana_tb')) {
                modal_ba_pengembalian_dana_tb.clear().draw();
                $("#modal_ba_pengembalian_dana_tb").dataTable().fnDestroy();
                $("#modal_ba_pengembalian_dana_tb thead").empty()
            }

            modal_ba_pengembalian_dana_tb = $('#modal_ba_pengembalian_dana_tb').DataTable({
                data: response.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 500px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "tipe_bayar", title: "Tipe Bayar" },
                    { data: "no_pb", title: "No. PB" },
                    { data: "tgl_pb", title: "Tgl. PB" },
                    { data: "kode_member", title: "Kode Member" },
                    { data: "total", title: "Total" },
                    { data: "ba", title: "BA" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function (row, data) {
                    $('td:eq(5)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-pengembalian" ${data.ba == 1 ? 'checked' : ''} name="ba-checkbox">`);
                }
            });

            if(isHistory == 1){
                $("#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table").attr("checked", true);
            } else {
                $("#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table").attr("checked", false);
            }
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_ba_pengembalian_dana_tb')) {
                modal_ba_pengembalian_dana_tb.clear().draw();
            }
        }
    });
}

function actionAdditionalBAPengembalianDanaPrepCetak(){
    var isHistory = $("#modal_ba_pengembalian_dana_checkbox").val();
    var swalText = isHistory == 0 
    ? "Yakin akan Melakukan Cetak Pengembalian Dana SPI berdasarkan data yang dipilih ?" 
    : "Yakin akan Melakukan Cetak Pengembalian Dana SPI berdasarkan History BA ?";

    Swal.fire({
        title: 'Yakin?',
        html: swalText,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            if(isHistory == 1){
                if($("#modal_ba_pengembalian_dana_select").val() !== ""){   
                    var data = [];
                    data.push({noBA : $("#modal_ba_pengembalian_dana_select").val()});
                    data.push({tglBA : $('#modal_ba_pengembalian_dana_select option:selected').attr('date-value')});
                    actionAdditionalBAPengembalianDanaCetak(isHistory, data);
                } else {
                    Swal.fire("Peringatan!", "Belum ada BA yang dipilih!", "warning");
                    return;
                }
            } else {
                if($('#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table:checked').length == 0){
                    Swal.fire("Peringatan!", "Belum Ada BA yg dipilih!", "warning");
                    return;
                }
                var data = getCheckedBAPengembalianDana();
                actionAdditionalBAPengembalianDanaCetak(isHistory, data);
            }
        }
    });
}

function actionAdditionalBAPengembalianDanaCetak(isHistory = 0, params = []){

    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/BAPengembalianDana",
        type: "POST",
        data: { isHistory: isHistory, data: params },
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            actionGlobalDownloadPdf(response.data.nama_file);
            Swal.fire("Success!", response.message, "success").then(function(){
                $("#modal_ba_pengembalian_dana").modal("hide");
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalRemoveMasterData(){
    var selectedRow = modal_master_data_tb.row(".select-r").data();
    Swal.fire({
        title: 'Yakin?',
        html: `Hapus Nomor Polisi ${selectedRow.no_polisi} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + "/action/actionMasterAlasanBatalKirimRemove",
                type: "POST",
                data: { data: selectedRow.no_polisi },
                success: function(response) {
                    setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                    Swal.fire("Success!", response.message, "success");
                    actionAdditionalQueryDatatablesMasterData($("#modal_master_data_flag_mode").val());
                }, error: function(jqXHR, textStatus, errorThrown) {
                    setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                            ? jqXHR.responseJSON.message
                            : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            });
        }
    });
}

function actionAdditionalListingDeliveryDatatables(noPB = 0){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionListingDeliveryDatatables/",
        type: "GET",
        data: {no_pb: noPB},
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide');     }, 500);
            if ($.fn.DataTable.isDataTable('#modal_listing_delivery_tb')) {
                modal_listing_delivery_tb.clear().draw();
                $("#modal_listing_delivery_tb").dataTable().fnDestroy();
                $("#modal_listing_delivery_tb thead").empty()
            }

            modal_listing_delivery_tb = $('#modal_listing_delivery_tb').DataTable({
                data: response.data.dtData,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 700px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "tipe_bayar", title: "Tipe Bayar" },
                    { data: "no_pb", title: "No. PB" },
                    { data: "tgl_pb", title: "Tgl. PB" },
                    { data: "kode_member", title: "Kode Member" },
                    { data: "kirim", title: "Kirim" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function (row, data) {
                    $('td:eq(4)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-listing-delivery" ${data.kirim == 1 ? 'checked' : ''}>`);
                }
            });

            $("#modal_listing_delivery_tb tbody tr td input.checkbox-table").attr("checked", false);

            $("#modal_listing_delivery_history").val(response.data.isHistory);
            $("#modal_listing_delivery_nolist").val(response.data.nolist);
            $("#modal_listing_delivery_tgllist").val(response.data.tgllist);

            $("#modal_listing_delivery_nopol").val(response.data.headerInfo.nopol).prop("disabled", true);
            $("#modal_listing_delivery_driver").val(response.data.headerInfo.driver).prop("disabled", true);
            $("#modal_listing_delivery_deliveryman").val(response.data.headerInfo.delimen).prop("disabled", true);
            $("#modal_listing_delivery .modal-dialog .modal-footer .btn-primary").prop("disabled", false);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#tb_bukti_stk')) {
                modal_listing_delivery_tb.clear().draw();
            }
            $("#modal_listing_delivery_nopol").val("").prop("disabled", true);
            $("#modal_listing_delivery_driver").val("").prop("disabled", true);
            $("#modal_listing_delivery_deliveryman").val("").prop("disabled", true);
            $("#modal_listing_delivery .modal-dialog .modal-footer .btn-primary").prop("disabled", true);
        }
    });
}

function actionAdditionalListingDeliveryPrepCetak(){
    var swalText = "Yakin akan Melakukan Cetak Listing Deliver berdasarkan data yang dipilih ?";

    Swal.fire({
        title: 'Yakin?',
        html: swalText,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            if($('#modal_listing_delivery_tb tbody tr td input.checkbox-table:checked').length == 0){
                Swal.fire("Peringatan!", "Belum Ada Delivery yg dipilih!", "warning");
                return;
            }
            var data = getCheckedListingDelivery();
            actionAdditionalListingDeliveryCetak(data);
        }
    });
}

function actionAdditionalListingDeliveryCetak(params = []){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/ListingDelivery",
        type: "POST",
        data: { isHistory: $("#modal_listing_delivery_history").val(), noList: $("#modal_listing_delivery_nolist").val(), tglList: $("#modal_listing_delivery_tgllist").val(), nopol: $("#modal_listing_delivery_nopol").val(), driver: $("#modal_listing_delivery_driver").val(), deliveryman: $("#modal_listing_delivery_deliveryman").val(), data: params },
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            actionGlobalDownloadPdf(response.data.nama_file);
            Swal.fire("Success!", response.message, "success").then(function(){
                $("#modal_listing_delivery").modal("hide");
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingSimpan(){
    var data = getCheckedMasterPicking();
    if(data.length <= 0){
        Swal.fire("Peringatan!", "Rak Belum Dipilih", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterPickingHHSimpan",
        type: "POST",
        data: { data: data, userid: $("#modal_master_picking_users").val(), group: $("#modal_master_picking_group").val() },
        success: function(response) {
            actionAdditionalMasterPickingHHLoadRakUser(false); //? Datatables
            actionAdditionalMasterPickingHHLoadRakAll(false); //? Datatables
            $('#modal_master_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
            $("#modal_loading").modal("hide");
            Swal.fire("Success!", "Berhasil Menyimpan Data!", "success");
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingHapus(){
    var data = getSelectedMasterPicking2();
    if(data.length <= 0){
        Swal.fire("Peringatan!", "Rak Belum Dipilih", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterPickingHHHapus",
        type: "POST",
        data: { data: data, userid: $("#modal_master_picking_users").val()},
        success: function(response) {
            actionAdditionalMasterPickingHHLoadRakUser(false); //? Datatables
            actionAdditionalMasterPickingHHLoadRakAll(false); //? Datatables
            $('#modal_master_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
            $("#modal_loading").modal("hide");
            Swal.fire("Success!", response.message, "success");
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingAddGroup(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterPickingHHAddGroup",
        type: "GET",
        success: function(response) {
            $("#modal_master_group_picking").modal("show");
            $("#modal_title_master_group_picking").text(response.data.lblTitle);
            setTimeout(() => {
                actionAdditionalMasterPickingFilterGroup(false); //? Datatables
            }, 500);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingFilterGroup(disableLoading = true){
    $('#modal_loading').modal('show');
    $("#modal_master_group_picking_input").empty();
    $.ajax({
        url: currentURL + `/action/actionMasterPickingFilterGroup`,
        type: "GET",
        async: false,
        success: function(response) {
            $("#modal_master_group_picking").attr("data-status", "true");

            $("#modal_master_group_picking_grup").append(`<option value="ALL">ALL</option>`)
            response.data.data.forEach(item => {
                $("#modal_master_group_picking_input").append(`<option value="${item.group}">${item.group}</option>`)
                $("#modal_master_group_picking_grup").append(`<option value="${item.group}">${item.group}</option>`)
            });

            $("#modal_master_group_picking_input").trigger("change");
            $("#modal_master_group_picking_grup").trigger("change");
            actionAdditionalMasterPickingLoadUser(false);
            actionAdditionalMasterPickingGroupPicking(false);
            $("#modal_master_group_picking").attr("data-status", "false");
            $('#modal_loading').modal('hide');
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

function actionAdditionalMasterPickingLoadUser(disableLoading = true){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionMasterPickingLoadUser`,
        type: "GET",
        async: false,
        success: function(response) {
            if(disableLoading){
                setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            }
            if ($.fn.DataTable.isDataTable('#modal_master_picking_group_tb1')) {
                modal_master_picking_group_tb1.clear().draw();
                $("#modal_master_picking_group_tb1").dataTable().fnDestroy();
                $("#modal_master_picking_group_tb1 thead").empty()
            }

            modal_master_picking_group_tb1 = $('#modal_master_picking_group_tb1').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 532px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "id", title: "ID" },
                    { data: "nama", title: "Nama" },
                    { data: "pick", title: "Pick" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function (row, data) {
                    $('td:eq(2)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-master-picking-group-1" ${data.pick == 1 ? 'checked' : ''}>`);
                }
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_master_picking_group_tb1')) {
                modal_master_picking_group_tb1.clear().draw();
            }
        }
    });
}

function actionAdditionalMasterPickingGroupSimpan(){
    var inputGroup = $("#modal_master_group_picking_input").val();
    var data = getCheckedMasterPickingGroup();
    if(inputGroup == ''){
        Swal.fire("Peringatan!", "Belum Input Group", "warning");
        return;
    }
    if(data.length <= 0){
        Swal.fire("Peringatan!", "User Picking Belum Dipilih", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterPickingGroupSimpan",
        type: "POST",
        data: { data: data, inputGroup: inputGroup},
        success: function(response) {
            setTimeout(() => {
                actionAdditionalMasterPickingFilterGroup(false); //? Datatables
                $("#modal_loading").modal("hide");
                Swal.fire("Success!", response.message, "success");
            }, 500);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingGroupHapus(){
    var data = getCheckedMasterPickingGroup2();
    if(data.length <= 0){
        Swal.fire("Peringatan!", "User Picking Belum Dipilih", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionMasterPickingGroupHapus",
        type: "POST",
        data: { data: data},
        success: function(response) {
            setTimeout(() => {
                actionAdditionalMasterPickingFilterGroup(false); //? Datatables
                $("#modal_loading").modal("hide");
                Swal.fire("Success!", response.message, "success");
            }, 500);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionAdditionalMasterPickingGroupPicking(disableLoading = true){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionMasterPickingLoadPicking/${$("#modal_master_group_picking_grup").val()}`,
        type: "GET",
        async: false,
        success: function(response) {
            if(disableLoading){
                setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            }
            if ($.fn.DataTable.isDataTable('#modal_master_picking_group_tb2')) {
                modal_master_picking_group_tb2.clear().draw();
                $("#modal_master_picking_group_tb2").dataTable().fnDestroy();
                $("#modal_master_picking_group_tb2 thead").empty()
            }

            modal_master_picking_group_tb2 = $('#modal_master_picking_group_tb2').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 532px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "grup", title: "Grup" },
                    { data: "id", title: "ID" },
                    { data: "nama", title: "Nama" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function(row, data){
                    $(row).click(function() {
                        $(this).toggleClass("select-r");
                    });
                },
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_master_picking_group_tb2')) {
                modal_master_picking_group_tb2.clear().draw();
            }
        }
    });
}

function actionAdditionalBuktiSerahTerimaKardusDatatables(isHistory = 0){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionBuktiSerahTerimaKardusDatatables/" + isHistory,
        type: "GET",
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            if ($.fn.DataTable.isDataTable('#tb_bukti_stk')) {
                tb_bukti_stk.clear().draw();
                $("#tb_bukti_stk").dataTable().fnDestroy();
                $("#tb_bukti_stk thead").empty()
            }

            tb_bukti_stk = $('#tb_bukti_stk').DataTable({
                data: response.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 500px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "no_pb", title: "No. PB" },
                    { data: "tgl_pb", title: "Tgl. PB" },
                    { data: "kode_member", title: "Kode Member" },
                    { data: "cetak", title: "Cetak" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function (row, data) {
                    $('td:eq(3)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-stk" ${data.cetak == 1 ? 'checked' : ''} name="ba-checkbox">`);
                }
            });

            if(isHistory == 1){
                $("#tb_bukti_stk tbody tr td input.checkbox-table").attr("checked", true);
            } else {
                $("#tb_bukti_stk tbody tr td input.checkbox-table").attr("checked", false);
            }
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#tb_bukti_stk')) {
                tb_bukti_stk.clear().draw();
            }
        }
    });
}

function actionAdditionalBuktiSerahTerimaKardusPrepCetak(){
    var isHistory = $("#cek_history_stk").val();
    var swalText = isHistory == 0 
    ? "Yakin akan Melakukan Cetak Serah Terima Kardus berdasarkan data yang dipilih ?" 
    : "Yakin akan Melakukan Cetak Serah Terima Kardus berdasarkan History STK ?";

    Swal.fire({
        title: 'Yakin?',
        html: swalText,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            if(isHistory == 1){
                if($("#select_history_stk").val() !== ""){   
                    var data = [];
                    data.push({noSTK : $("#select_history_stk").val()});
                    data.push({tglSTK : $('#select_history_stk option:selected').attr('date-value')});
                    actionAdditionalBuktiSerahTerimaKardusCetak(isHistory, data);
                } else {
                    Swal.fire("Peringatan!", "Belum ada History yang dipilih!", "warning");
                    return;
                }
            } else {
                if($('#tb_bukti_stk tbody tr td input.checkbox-table:checked').length == 0){
                    Swal.fire("Peringatan!", "Belum Ada Data yg dipilih!", "warning");
                    return;
                }
                var data = getCheckedSerahTerimaKardus();
                actionAdditionalBuktiSerahTerimaKardusCetak(isHistory, data);
            }
        }
    });
}

function actionAdditionalBuktiSerahTerimaKardusCetak(isHistory = 0, params = []){

    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/BuktiSerahTerimaKardus",
        type: "POST",
        data: { isHistory: isHistory, data: params, isShowDatatables: 0 },
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            actionGlobalDownloadPdf(response.data.nama_file);
            Swal.fire("Success!", response.message, "success").then(function(){
                $("#modal_stk").modal("hide");
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function detailTransaksi(element){
    var selectedRow = tb.row($(element).closest('tr')).data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/detail-transaksi`,
        type: "POST",
        data: {selectedRow: selectedRow, tanggal_trans: $("#tanggal_trans").val()},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            $("input.input-detail-transaksi").each(function() {
                var elem_id = $(this).attr("id");
                $(this).val(response.data[elem_id]);
            });
            $("#lbl_pembayaranVA_detail_transaksi_tab3").text(response.data.lbl_pembayaranVA_detail_transaksi_tab3)
            $("#modal_detail_transaksi").modal("show");
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

function actionSendHandheld(DonePilihJalurPicking = false){
    var selectedRow = tb.row(".select-r").data();
    if(DonePilihJalurPicking){
        $('#modal_loading').modal('show');
        $('input[name="input_jalur_picking"]:checked').val();
        $("#modal_pilih_jalur_picking").modal("hide");
        $.ajax({
            url: currentURL + `/action/SendHandHelt`,
            type: "POST",
            data: {no_trans: selectedRow.no_trans, status: selectedRow.status, statusSiapPicking: statusSiapPicking, pilihan: $('input[name="input_jalur_picking"]:checked').val(), nopb: selectedRow.no_pb, tanggal_pb: selectedRow.tgl_pb, kode_member: selectedRow.kode_member, tanggal_trans: $("#tanggal_trans").val(), pickRakToko: $("#pick_rak_toko").val()},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success');
                if(response.data.content !== "noTXT"){
                    var blob = new Blob([response.data.content], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = response.data.nama_file;
                    link.click();
                }
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                if(jqXHR.responseJSON.code === 401){
                    Swal.fire('Peringatan!', jqXHR.responseJSON.message,'error');
                    var blob = new Blob([jqXHR.responseJSON.data.content], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = jqXHR.responseJSON.data.nama_file;
                    link.click();
                } else {
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            }
        });
    } else {
        Swal.fire({
            title: 'Yakin?',
            text: "Send Jalur No Trans " + selectedRow.no_trans + " ini ?",
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                if(statusSiapPicking !== selectedRow.status){
                    Swal.fire("Peringatan!", "Bukan Data Yang Siap Send Jalur!", "warning");
                    return;
                }
                if($("#tanggal_trans").val() == ''){
                    Swal.fire("Peringatan!", "Pilih Tanggal Trans Terlebih Dahulu", "warning");
                    return;
                }
                $('input[name="input_jalur_picking"]').first().prop("checked", true).trigger('change');
                $("#modal_pilih_jalur_picking").modal("show");
            }
        });
    }
}

function actionOngkosKirim(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/OngkosKirim`,
        type: "POST",
        data: {no_trans: selectedRow.no_trans, status: selectedRow.status, flagBayar: selectedRow.flagbayar, nopb: selectedRow.no_pb, tanggal_pb: selectedRow.tgl_pb, freeOngkir: selectedRow.free_ongkir, jarakKirim: selectedRow.jarakkirim, kode_member: selectedRow.kode_member},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            if(response.code === 200){
                Swal.fire('Success!', response.message,'success');
                tb.ajax.reload();
            } else if (response.code === 201){
                $("#txtNama_modal_ekspedisi").val("");
                $("#txtHarga_modal_ekspedisi").val("0");
                $("#kgberat_modal_ekspedisi").val(response.data.jarakKirim);
                if(parseInt(response.data.jarakKirim) > 0){
                    $("#kgberat_modal_ekspedisi").attr("disabled", true);
                } else {
                    $("#kgberat_modal_ekspedisi").attr("disabled", false);
                }
                if(response.data.flagFree == false || response.data.flagFree == 'false'){
                    $("#img_gratis_ongkir_modal_ekspedisi").addClass("d-none");
                    $("#img_gratis_ongkir_modal_ekspedisi").removeClass("d-flex");
                } else {
                    $("#img_gratis_ongkir_modal_ekspedisi").removeClass("d-none");
                    $("#img_gratis_ongkir_modal_ekspedisi").addClass("d-flex");
                }
                $("#cbEks_modal_ekspedisi").empty();
                $("#cbNamaEks_modal_ekspedisi").empty();
                response.data.namaEkspedisi1.forEach(item => {
                    $("#cbEks_modal_ekspedisi").append(`<option value="${item.eks_kodeekspedisi}">${item.eks_namaekspedisi}</option>`);
                });
                response.data.namaEkspedisi2.forEach(item => {
                    $("#cbNamaEks_modal_ekspedisi").append(`<option value="${item.id}">${item.title}</option>`);
                });
                $("#BtnOK_modal_ekspedisi").addClass("d-none");
                $("#cbPengirim_modal_ekspedisi option:first").prop("selected", true).trigger("change");
                $("#modal_ekspedisi").modal("show");
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

$("#cbPengirim_modal_ekspedisi").change(function(){
    if($(this).val() == "EKSPEDISI"){
        $("#lblNama_modal_ekspedisi").text("Nama Ekspedisi");
        $("#lblJarak_modal_ekspedisi").text("Ongkos Kirim");

        $("#cbEks_modal_ekspedisi").addClass("d-none");
        $("#cbNama_modal_ekspedisi").removeClass("d-none");
        $("#txtNama_modal_ekspedisi").addClass("d-none")

        $("#kgberat_modal_ekspedisi").addClass("d-none");
        $("#txtHarga_modal_ekspedisi").removeClass("d-none");
        $("#txtHarga_modal_ekspedisi").val("0");

        if($("modal_ekspedisi").attr("data-simulasi") == true){
            if(!$("#img_gratis_ongkir_modal_ekspedisi").hasClass("d-none")){
                $("#txtHarga_modal_ekspedisi").attr("disabled", true);
            } else {
                $("#txtHarga_modal_ekspedisi").attr("disabled", false);
            }
        }
    } else {
        $("#lblNama_modal_ekspedisi").text("Jenis Kendaraan");
        $("#lblJarak_modal_ekspedisi").text("Jarak");

        $("#cbEks_modal_ekspedisi").removeClass("d-none");
        $("#cbNama_modal_ekspedisi").addClass("d-none");
        $("#txtNama_modal_ekspedisi").addClass("d-none")

        $("#kgberat_modal_ekspedisi").removeClass("d-none");
        $("#txtHarga_modal_ekspedisi").addClass("d-none");
        $("#txtHarga_modal_ekspedisi").val($("#kgberat_modal_ekspedisi").val());
        if($("#kgberat_modal_ekspedisi").val() > 0){
            $("#kgberat_modal_ekspedisi").attr("disabled", true);
        } else {
            $("#kgberat_modal_ekspedisi").attr("disabled", false);
        }
    }
});

$("#showBtn_modal_ekspedisi").click(function(){
    $("#rincian_biaya_modal_ekspedisi").val("");
    if($("#cbPengirim_modal_ekspedisi").val() == "EKSPEDISI"){
        $("#txtNama_modal_ekspedisi").val($("#cbNamaEks_modal_ekspedisi").val());
        if($("#txtNama_modal_ekspedisi").val() == ""){
            Swal.fire("Peringatan!", "Nama Ekspedisi Belum Anda Input.", "warning");
            return;
        }
    } else {
        if($("#kgberat_modal_ekspedisi").val() == ""){
            Swal.fire("Peringatan!", "Jarak Pengiriman Belum Anda Input.", "warning");
            return;
        } else {
            if(parseFloat($('#kgberat_modal_ekspedisi').val()) == 0){
                Swal.fire("Peringatan!", "Jarak pengiriman Belum Anda Input.");
                return;
            }
        }

        hitungBiaya()
    }
});

function hitungBiaya(){
    $("#modal_ekspedisi").attr("data-ongkos", "0");
    $("#modal_ekspedisi").attr("data-zona", "1");
    var kodeEkspedisi, Ongkos, Jarak;

    if($("#cbPengirim_modal_ekspedisi").val() == "EKSPEDISI"){
        kodeEkspedisi = $("#txtNama_modal_ekspedisi").val();

        Jarak = parseFloat($('#kgberat_modal_ekspedisi').val().replace('.', ','));
        Ongkos = parseFloat($('#txtHarga_modal_ekspedisi').val().replace('.', ''));
    } else {
        kodeEkspedisi = $("#cbEks_modal_ekspedisi").val();
        Jarak = parseFloat($('#kgberat_modal_ekspedisi').val().replace('.', ','));
        $('#modal_loading').modal('show');
        var temp_ongkos;
        $.ajax({
            url: currentURL + `/action/getOngkosHitungBiaya`,
            type: "GET",
            async: false,
            data: { kodeEkspedisi: kodeEkspedisi, Jarak: Jarak },
            success: function(response) {
                setTimeout(function() { $('#modal_loading').modal('hide'); }, 500);
                console.log(response.data[0]);
                temp_ongkos = response.data[0].harga;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function() { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
                temp_ongkos = 0
            }
        });
        Ongkos = temp_ongkos;
    }

    if(Ongkos == -1){
        Swal.fire("Peringatan!", "Inputan Jarak Melebihi Jarak Makismal.", "warning");
        return;
    } else {
        if(!$("#img_gratis_ongkir_modal_ekspedisi").hasClass("d-none")){
            Ongkos = 0;
        }

        $("#rincian_biaya_modal_ekspedisi").val("");
        $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + "RINCIAN BIAYA PENGIRIMAN \n");
        $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + "=========================== \n");
        if ($("#cbPengirim_modal_ekspedisi").val() == "EKSPEDISI") {
            $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + `Nama Ekspedisi : ${$("#txtNama_modal_ekspedisi").val().toUpperCase()}\n`);
        } else {
            $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + `Jenis Kendaraan : ${$("#cbEks_modal_ekspedisi").val().toUpperCase()}\n`);
            $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + `Jarak : ${Jarak}\n`);
        }
        $("#rincian_biaya_modal_ekspedisi").val($("#rincian_biaya_modal_ekspedisi").val() + `Ongkos Kirim : ${fungsiRupiah(Ongkos)}\n`);

        if($("modal_ekspedisi").attr("data-simulasi") == false){
            $("#BtnOK_modal_ekspedisi").removeClass("d-none");
            $("#BtnOK_modal_ekspedisi").attr("disabled", false);
        }
    }
}

function actionDraftStruk(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/DraftStruk`,
        type: "POST",
        data: { kode_web: selectedRow.kodeweb, tanggal_trans: $("#tanggal_trans").val(), selectedRow: selectedRow },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            actionGlobalDownloadZip(response.data.pathStorage, "Draft-Struk.zip");
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

function actionPembayaranVA(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/PembayaranVA`,
        type: "POST",
        data: {tipe_bayar: selectedRow.tipe_bayar, tanggal_pb: selectedRow.tgl_pb, nopb: selectedRow.no_pb, no_trans: selectedRow.no_trans, kode_member: selectedRow.kode_member},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            var data = response.data;
            $("#bank_modal_pembayaran_va").empty();
            $("#no_pb_modal_pembayaran_va").val(data.request.nopb);
            $("#tgl_pb_modal_pembayaran_va").val(data.request.tanggal_pb);
            $("#no_trans_modal_pembayaran_va").val(data.request.no_trans);
            $("#ammount_modal_pembayaran_va").text(data.dt[0].total_bayar);
            $("#btn_refresh_modal_pembayaran_va").attr("urlCekPaymentChangeStatus", data.urlCekPaymentChangeStatus);
            $("#btn_proses_modal_pembayaran_va").attr("urlCreatePaymentChange", data.urlCreatePaymentChange);
            if(data.data_transaksi.length > 0){
                $("#no_va_modal_pembayaran_va").text(data.data_transaksi[0].tva_nomorva);
                $("#status_modal_pembayaran_va").text(data.data_transaksi[0].status);
                $("#bank_modal_pembayaran_va").append($('<option></option>').val(data.data_transaksi[0].tva_bank.id).text(data.data_transaksi[0].tva_bank.payment_type));
                $("#bank_modal_pembayaran_va").attr("disabled", true);
                if(data.data_transaksi[0].status.toUpperCase() == "PEMBAYARAN SUDAH DITERIMA"){
                    actionAdditionalPembayaranVaChangeButton();
                    $("#btn_refresh_modal_pembayaran_va").attr("disabled", true);
                } else {
                    changeButton();
                }
            } else{
                $("#bank_modal_pembayaran_va").attr("disabled", false);
                $("#no_va_modal_pembayaran_va").text("XXXX");
                $("#status_modal_pembayaran_va").text("Pembayaran Belum Diterima");
                (async () => {
                    var dataValue = await connectToWebService(data.urlMasterPayment, "POST");
                    if (dataValue) {
                        const $selectElement = $('#bank_modal_pembayaran_va');
                        dataValue.forEach(item => {
                            const $option = $('<option></option>').val(item.id).text(item.payment_type);
                            $selectElement.append($option);
                        });
                    }
                })();
            }
            $("#modal_pembayaran_va").modal("show");
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

function actionKonfirmasiPembayaran(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/KonfirmasiPembayaran`,
        type: "POST",
        data: { no_trans: selectedRow.no_trans, status: selectedRow.status, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire("Success", "Konfirmasi Pembayaran Berhasil", "success");
            var blob = new Blob([response.data], { type: "text/plain" });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "WRITE_SSO.txt";
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

function actionSales(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/Sales`,
        type: "POST",
        data: { no_trans: selectedRow.no_trans, status: selectedRow.status, nopb: selectedRow.no_pb, tipe_bayar: selectedRow.tipe_bayar, tanggal_pb: selectedRow.tgl_pb, kode_web: selectedRow.kodeweb, kode_member: selectedRow.kode_member, tipe_kredit: selectedRow.tipe_kredit, tanggal_trans: $("#tanggal_trans").val(), selectedRow: selectedRow },
        success: function(response) {
            actionGlobalDownloadZip(response.data.pathStorage, "SALES.zip");
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

function actionCetakSuratJalan(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/CetakSuratJalan`,
        type: "POST",
        data: { tanggal_trans: $("#tanggal_trans").val(), selectedRow: selectedRow },
        success: function(response) {
            actionGlobalDownloadPdf(response.data.nama_file);
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

function actionCetakIIK(){
    var selectedRow = tb.row(".select-r").data();
    Swal.fire({
        title: 'Yakin?',
        html: `Cetak Informasi Koli No Trans = ${selectedRow.no_trans} Ini?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/CetakIIK`,
                type: "POST",
                data: { selectedRow: selectedRow },
                success: function(response) {
                    Swal.fire('Success!', response.message,'success');
                    actionGlobalDownloadZip(response.data.pathStorage, "CETAK-IIK.zip");
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
    });
}

function actionPbBatal(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/PbBatal`,
        type: "POST",
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            var data = response.data;
            $("#modal_list_pb_batal .modal-dialog").addClass("modal-lg")
            $("#modal_list_pb_batal .modal-dialog").removeClass("modal-xl")
            $("#label_item_batal").toggleClass("d-none", !data.labelItemBatal);
            $(".btn-action[actionname='PbBatal']").toggleClass("d-none", !data.btnPBBatal);
            $("#label_item_batal").toggleClass("d-none", !data.labelItemBatal);
            if(data.showForm){
                $("#modal_list_pb_batal").modal("show");
                if(data.type == "PB"){
                    var columnsData = [
                        { data: 'kodemember', title: 'Kode Member' },
                        { data: 'nopb', title: 'No. PB' },
                        { data: 'tglpb', title: 'Tgl PB' },
                    ]
                    $("#modal_list_pb_batal_title").text("Daftar PB Yang Sudah Lewat Hari Belum Diproses");
                } else {
                    var columnsData = [
                        { data: 'no_transaksi', title: 'No. Transaksi' },
                        { data: 'tgl_transaksi', title: 'Tgl. Transaksi' },
                    ]
                    $("#modal_list_pb_batal_title").text("Daftar PB Item Batal Yang Belum Dikembalikan ke Rak");
                }
                
                if ($.fn.DataTable.isDataTable('#modal_list_pb_batal_tb')) {
                    tb_periode_pesanan.clear().draw();
                    $("#modal_list_pb_batal_tb").dataTable().fnDestroy();
                    $("#modal_list_pb_batal_tb thead").empty()
                }
                initialize_datatables_list_pb(data.data, columnsData)
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

function actionBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/BaRusakKemasan`,
        type: "POST",
        data: { selectedRow: selectedRow },
        success: function(response) {
            $("#modal_ba_barang_rusak").modal("show");
            var nama_member;
            if(response.data.nama_member.length < 1){
                nama_member = "";
            } else {
                nama_member = response.data.nama_member[0].cus_namamember;
            }
            let fieldsResponseTemporary = [
                { id: "no_pb_ba_barang_rusak_tab", value: response.data.selectedRow.no_pb },
                { id: "kode_member_ba_barang_rusak_tab", value: response.data.selectedRow.kode_member },
                { id: "nama_member_ba_barang_rusak_tab", value: nama_member }
            ];
            for (let i = 1; i <= 3; i++) {
                fieldsResponseTemporary.forEach(field => {
                    $(`#${field.id}${i}`).val(field.value);
                });
            }
            
            actionAdditionalReloadTabBaRusakKemasan();
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

function actionGlobalSettingsTabBaRusakKemasan(params = 5) {
    const tabs = ["#input-ba-rk-tab", "#draft-ba-rk-tab", "#history-ba-rk-tab"];
    const errorMessage = () => Swal.fire("Error", "Pembuatan Tab Gagal Harap Coba Kembali", "error");

    if (params < 0 || params > 2) {
        return errorMessage();
    }

    tabs.forEach((tab, index) => {
        $(tab).toggleClass("d-none", index !== params);
        if (index === params) {
            $(tab).trigger("click");
        }
    });
}


function actionAdditionalReloadTabBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    $("#ba_barang_rusak_reprint").addClass('d-none');
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionBaRusakKemasanPrep`,
        type: "GET",
        data: { selectedRow: selectedRow },
        success: function(response) {
            $("#status_ba_barang_rusak_tab3").val(response.data.statusBA);
            $("#no_ba_barang_rusak_tab2").val(response.data.noBA)
            if(response.data.statusBA == "NEW"){
                $("#alasan_ba_barang_rusak_tab2").val(response.data.txtAlasan2);
                $("#alasan_ba_barang_rusak_tab3").val(response.data.txtAlasan3);
                actionGlobalSettingsTabBaRusakKemasan(0);
                $("#ba_barang_rusak_hitung_ulang").attr("disabled", false);
                $("#ba_barang_rusak_simpan").attr("disabled", true);
                if(selectedRow.tipe_bayar == "COD" && selectedRow.status == "Selesai Struk"){
                    $("#ba_barang_rusak_hitung_ulang").attr("disabled", true);
                    Swal.fire("Peringatan!", "Transaksi COD Sudah Selesai Struk", "warning");
                }
                setTimeout(() => {
                    loadItemPbBaRusakKemasan();
                }, 500);
                return;
            } else {
                $("#alasan_ba_barang_rusak_tab3").val(response.data.txtAlasan1);
                if(response.data.statusBA == "DRAFT"){
                    actionGlobalSettingsTabBaRusakKemasan(1);
                    if(selectedRow.tipe_bayar == "COD" && selectedRow.status == "Selesai Struk"){
                        $("#ba_barang_rusak_approve").attr("disabled", true);
                        Swal.fire("Peringatan!", "Transaksi COD Sudah Selesai Struk", "warning");
                    }
                } else{
                    actionGlobalSettingsTabBaRusakKemasan(2);
                    if(selectedRow.status == "BATAL"){
                        $("#ba_barang_rusak_reprint").attr("disabled", false);
                    }
                }
                setTimeout(() => {
                    loadItemBaBaRusakKemasan();
                }, 500);
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

function loadItemPbBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionBaRusakKemasanLoadItem`,
        type: "GET",
        async: false,
        data: {selectedRow: selectedRow},
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab1')) {
                tb_ba_barang_rusak_tab1.clear().draw();
                $("#tb_ba_barang_rusak_tab1").dataTable().fnDestroy();
                $("#tb_ba_barang_rusak_tab1 thead").empty()
            }

            tb_ba_barang_rusak_tab1 = $('#tb_ba_barang_rusak_tab1').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false, 
                "scrollY": "calc(100vh - 650px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "plu", title: "PLU" },
                    { data: "deskripsi", title: "Deskripsi" },
                    { data: "frac", title: "Frac" },
                    { data: "qtyba", title: "Qty BA" },
                    { data: "qtyreal", title: "Qty Real", render: function(data, type, row) {
                        return parseFloat(parseInt(data)).toFixed(0);
                    } },
                ],
                ordering: false,
                destory: true,
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab1')) {
                tb_ba_barang_rusak_tab1.clear().draw();
            }
        }
    });
}

function loadItemBaBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionBaRusakKemasanLoadBA`,
        type: "GET",
        async: false,
        data: {selectedRow: selectedRow},
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab2')) {
                tb_ba_barang_rusak_tab2.clear().draw();
                $("#tb_ba_barang_rusak_tab2").dataTable().fnDestroy();
                $("#tb_ba_barang_rusak_tab2 thead").empty()
            }

            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab3')) {
                tb_ba_barang_rusak_tab3.clear().draw();
                $("#tb_ba_barang_rusak_tab3").dataTable().fnDestroy();
                $("#tb_ba_barang_rusak_tab3 thead").empty()
            }

            tb_ba_barang_rusak_tab2 = $('#tb_ba_barang_rusak_tab2').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false, 
                "scrollY": "calc(100vh - 650px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "plu", title: "PLU" },
                    { data: "deskripsi", title: "Deskripsi" },
                    { data: "frac", title: "Frac" },
                    { data: "qtyba", title: "Qty BA", render: function(data, type, row) {
                        return parseFloat(parseInt(data)).toFixed(0);
                    } },
                ],
                ordering: false,
                destory: true,
            });

            tb_ba_barang_rusak_tab3 = $('#tb_ba_barang_rusak_tab3').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false, 
                "scrollY": "calc(100vh - 650px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "plu", title: "PLU" },
                    { data: "deskripsi", title: "Deskripsi" },
                    { data: "frac", title: "Frac" },
                    { data: "qtyba", title: "Qty BA", render: function(data, type, row) {
                        return parseFloat(parseInt(data)).toFixed(0);
                    } },
                ],
                ordering: false,
                destory: true,
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab2')) {
                tb_ba_barang_rusak_tab2.clear().draw();
            }
            if ($.fn.DataTable.isDataTable('#tb_ba_barang_rusak_tab3')) {
                tb_ba_barang_rusak_tab3.clear().draw();
            }
        }
    });
}


function hitungUlangBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    var datatable = tb_ba_barang_rusak_tab1.rows().data().toArray();
    Swal.fire({
        title: 'Yakin?',
        html: `Hitung Ulang ${selectedRow.no_pb} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/actionBaRusakKemasanHitungUlang`,
                type: "POST",
                data: {selectedRow: selectedRow, datatable: datatable},
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success');
                    var blob = new Blob([response.data.str], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = response.data.nama_file;
                    link.click();
                    $('#ba_barang_rusak_simpan').prop('disabled', false);
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
        } else {
            $('#ba_barang_rusak_simpan').prop('disabled', true);
        }
    });
}

function simpanBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    var datatable = tb_ba_barang_rusak_tab1.rows().data().toArray();
    Swal.fire({
        title: 'Yakin?',
        html: `Simpan Data BA RK ${selectedRow.no_pb} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/actionBaRusakKemasanSimpan`,
                type: "POST",
                data: {selectedRow: selectedRow, datatable: datatable, alasan: $("#alasan_ba_barang_rusak_tab1").val()},
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success').then(function(){
                        actionAdditionalReloadTabBaRusakKemasan();
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
    });
}

function approveBaRusakKemasanPrep(){
    $("#modal_ba_barang_rusak").addClass('brightness-blur');
    $("#userlevel_approval").val(991);
    $("#label_username_approval").text(getUserLevelNameApproval(991));
    $("#keterangan_approval").val("Approval Str Mgr./Jr.Mgr. - BA Rusak Kemasan");
    $("#modal_approval").modal("show");
}

function actionLanjutanApprovalBaRusakKemasan(username = "", namaUser = ""){
    var selectedRow = tb.row(".select-r").data();
    var datatable = tb_ba_barang_rusak_tab2.rows().data().toArray();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionBaRusakKemasanApprove`,
        type: "POST",
        data: {selectedRow: selectedRow, datatable: datatable, username: username, noBA: $("#no_ba_barang_rusak_tab2").val(), alasan: $("#alasan_ba_barang_rusak_tab2").val()},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire('Success!', response.message,'success').then(function(){
                actionAdditionalReloadTabBaRusakKemasan();
            })
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

function batalBaRusakKemasan(){
    var selectedRow = tb.row(".select-r").data();
    Swal.fire({
        title: 'Yakin?',
        html: `Batalin BA RK ${selectedRow.no_pb} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/actionBaRusakKemasanBatal`,
                type: "POST",
                data: {selectedRow: selectedRow},
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success').then(function(){
                        actionAdditionalReloadTabBaRusakKemasan();
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
    });
}

function actionItemPickingBelumTransit(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/ItemPickingBelumTransit`,
        type: "POST",
        success: function(response) {
            Swal.fire('Success!', response.message,'success');
            actionGlobalDownloadPdf(response.data);
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

function actionLoppCod(){
    $("#modal_sorting_lopp").modal("show");
    $("#modal_sorting_lopp_sortby1").val($('#modal_sorting_lopp_sortby1 option:first').val()).trigger('change');
    $("#modal_sorting_lopp_sortby2").val($('#modal_sorting_lopp_sortby2 option:first').val()).trigger('change');
}

function actionListPBLebihDariMaxSerahTerima(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/ListPBLebihDariMaxSerahTerima`,
        type: "POST",
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            if(response.data.showForm){
                $("#modal_list_pb_batal .modal-dialog").removeClass("modal-lg")
                $("#modal_list_pb_batal .modal-dialog").addClass("modal-xl")
                $("#modal_list_pb_batal_title").text("Daftar PB Yang Sudah Lewat Tanggal Max Serah Terima");
                $("#modal_list_pb_batal").modal("show");
                var columnsData = [
                    { data: 'tgl_pb', title: 'Tgl PB' },
                    { data: 'member', title: 'Member' },
                    { data: 'no_pb', title: 'No. PB' },
                    { data: 'no_trans', title: 'No. Trans' },
                    { data: 'max_serahterima', title: 'Max Serah Terima' },
                    { data: 'ekspedisi', title: 'Ekspedisi' },
                ]
                if ($.fn.DataTable.isDataTable('#modal_list_pb_batal_tb')) {
                    tb_periode_pesanan.clear().draw();
                    $("#modal_list_pb_batal_tb").dataTable().fnDestroy();
                    $("#modal_list_pb_batal_tb thead").empty()
                }
                initialize_datatables_list_pb(response.data.data, columnsData)
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

function actionMasterAlasanbatalKirim(){
    actionAdditionalShowModalMasterData("Master Alasan Batal Kirim", "No. Polisi", "AlasanBatalKirim");
}

function actionMasterPickingHH(){
    $("#modal_master_picking").modal("show");
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionMasterPickingHHPrep`,
        type: "POST",
        success: function(response) {
            $("#modal_title_master_picking").text(response.data.title);
            //* LOAD ALL DATA
            actionAdditionalMasterPickingHHLoadGroup(false);
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

function actionAdditionalMasterPickingHHLoadGroup(disableLoading = true){
    $('#modal_loading').modal('show');
    $("#modal_master_picking_group").empty();
    $.ajax({
        url: currentURL + `/action/actionMasterPickingHHLoadGroup`,
        type: "GET",
        async: false,
        success: function(response) {
            if(disableLoading){
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            }

            response.data.data.forEach(item => {
                $("#modal_master_picking_group").append(`<option value="${item.grup}">${item.grup}</option>`)
            });

            $("#modal_master_picking_group").trigger("change");
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

function actionAdditionalMasterPickingHHFilterRak(disableLoading = true){
    $('#modal_loading').modal('show');
    $("#modal_master_picking_kode_rak").empty();
    $.ajax({
        url: currentURL + `/action/actionMasterPickingHHFilterRak/` + $("#modal_master_picking_group").val(),
        type: "GET",
        async: false,
        success: function(response) {

            if(disableLoading){
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            }
            $("#modal_master_picking_kode_rak").append(`<option value="ALL" selected>ALL</option>`)
            response.data.data.forEach(item => {
                $("#modal_master_picking_kode_rak").append(`<option value="${item.lks_koderak}">${item.lks_koderak}</option>`)
            });
            $("#modal_master_picking_kode_rak").trigger("change");
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

function actionAdditionalMasterPickingHHLoadRakAll(disableLoading = true){
    $('#modal_loading').modal('show');
    var listRak = getListRakMasterPicking();
    var dataToSend = {
        listRak: listRak,
        group: $("#modal_master_picking_group").val(),
        kode_rak: $("#modal_master_picking_kode_rak").val()
    };
    $.ajax({
        url: currentURL + "/action/actionMasterPickingHHLoadRakAll/",
        type: "POST",    
        contentType: 'application/json',
        data: JSON.stringify(dataToSend),
        async: false,
        success: function(response) {
            if(disableLoading){
                setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            }
            if ($.fn.DataTable.isDataTable('#modal_master_picking_tb1')) {
                modal_master_picking_tb1.clear().draw();
                $("#modal_master_picking_tb1").dataTable().fnDestroy();
                $("#modal_master_picking_tb1 thead").empty()
            }

            modal_master_picking_tb1 = $('#modal_master_picking_tb1').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 500px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "koderak", title: "Kode Rak" },
                    { data: "kodesubrak", title: "Kode SubRak" },
                    { data: "pick", title: "Pick" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function (row, data) {
                    $('td:eq(2)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-master-picking-1" onchange="checkboxMasterPickingOnChange()" ${data.pick == 1 ? 'checked' : ''}>`);
                }
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_master_picking_tb1')) {
                modal_master_picking_tb1.clear().draw();
            }
        }
    });
}

//* action trigger onchange modal_master_picking_group
function actionAdditionalMasterPickingHHLoadUser(disableLoading = true){
    $('#modal_loading').modal('show');
    $("#modal_master_picking_users").empty();
    $.ajax({
        url: currentURL + `/action/actionMasterPickingHHLoadUser/` + $("#modal_master_picking_group").val(),
        type: "GET",
        async: false,
        success: function(response) {

            if(disableLoading){
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            }
            $("#modal_master_picking_users").append(`<option value="ALL" selected>ALL</option>`)
            response.data.data.forEach(item => {
                $("#modal_master_picking_users").append(`<option value="${item.userid}">${item.user}</option>`)
            });
            $("#modal_master_picking_users").trigger("change");
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

function actionAdditionalMasterPickingHHLoadRakUser(disableLoading = true){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionMasterPickingHHLoadRakUser/${$("#modal_master_picking_group").val()}/${$("#modal_master_picking_users").val()}`,
        type: "GET",
        async: false,
        success: function(response) {
            if(disableLoading){
                setTimeout(() => { $('#modal_loading').modal('hide'); }, 500);
            }
            if ($.fn.DataTable.isDataTable('#modal_master_picking_tb2')) {
                modal_master_picking_tb2.clear().draw();
                $("#modal_master_picking_tb2").dataTable().fnDestroy();
                $("#modal_master_picking_tb2 thead").empty()
            }

            modal_master_picking_tb2 = $('#modal_master_picking_tb2').DataTable({
                data: response.data.data,
                language: {
                    emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                },
                order: [],
                "paging": false,
                "searching": false,
                "scrollY": "calc(100vh - 532px)",
                "scrollCollapse": true,
                columnDefs: [{ className: 'text-center', targets: "_all" }],
                columns: [
                    { data: "urutan", title: "Urutan" },
                    { data: "koderak", title: "Kode Rak" },
                    { data: "kodesubrak", title: "Kode SubRak" },
                ],
                ordering: false,
                destory: true,
                rowCallback: function(row, data){
                    $(row).click(function() {
                        $(this).toggleClass("select-r");
                    });
                },
            });
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_master_picking_tb2')) {
                modal_master_picking_tb2.clear().draw();
            }
        }
    });
}

function actionListingDelivery(){
    var selectedRow = tb.row(".select-r").data();

    if(selectedRow.no_pb == ""){
        Swal.fire("Peringatan!", "Data Tidak Memiliki No. PB", "warning");
        return;
    }

    Swal.fire({
        title: 'Yakin?',
        html: `Cetak Listing Delivery NoPB ${selectedRow.no_pb} " ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/actionListingDeliveryPrep`,
                type: "POST",
                data: {selectedRow: selectedRow},
                success: function(response) {
                    $("#modal_listing_delivery_nopb").val(selectedRow.no_pb)
                    actionAdditionalListingDeliveryDatatables(selectedRow.no_pb);
                    $("#modal_listing_delivery").modal("show");
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
    });
}

function actionReCreateAWB(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/ReCreateAWB`,
        type: "POST",
        success: function(response) {
            $("#modal_re_create_awb").modal("show")
            setTimeout(() => {
                if ($.fn.DataTable.isDataTable('#modal_re_create_awb_tb')) {
                    modal_re_create_awb_tb.clear().draw();
                    $("#modal_re_create_awb_tb").dataTable().fnDestroy();
                    $("#modal_re_create_awb_tb thead").empty()
                }
    
                modal_re_create_awb_tb = $('#modal_re_create_awb_tb').DataTable({
                    data: response.data.data,
                    language: {
                        emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
                    },
                    order: [],
                    "paging": false,
                    "searching": false,
                    "scrollY": "calc(100vh - 532px)",
                    "scrollCollapse": true,
                    columnDefs: [{ className: 'text-center', targets: "_all" }],
                    columns: [
                        { data: "kdmember", title: "KODE MEMBER" },
                        { data: "notrans", title: "NO. TRANS" },
                        { data: "tgltrans", title: "TGL TRANS" },
                        { data: "nopb", title: "NO. PB" },
                        { data: "tgltrans", title: "ALASAN BATAL", render: function(data, type, row) { return response.data.alasanBatal }},
                    ],
                    ordering: false,
                    destory: true,
                    rowCallback: function(row, data){
                        $(row).click(function() {
                            $(this).toggleClass("select-r");
                        });
                    },
                });
    
                $('#modal_loading').modal('hide');
                // $('#modal_re_create_awb_tb').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
            }, 500);
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
            if ($.fn.DataTable.isDataTable('#modal_re_create_awb_tb')) {
                modal_re_create_awb_tb.clear().draw();
            }
        }
    });
}

function actionAdditionalReCreateAWBProses(){
    var data = getCheckedReCreateAWB();
    if(data.length <= 0){
        Swal.fire("Peringatan!", "Belum ada PB yang dipilih", "warning");
        return;
    }
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + "/action/actionReCreateAWBProses",
        type: "POST",
        data: { data: data },
        success: function(response) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            $("#modal_re_create_awb").modal("hide")
            Swal.fire("Success!", response.message, "success");
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionBAPengembalianDana(){
    $("#modal_ba_pengembalian_dana_select").empty();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/actionBAPengembalianDanaGetHistory`,
        type: "GET",
        success: function(response) {
            $("#modal_ba_pengembalian_dana_checkbox").prop("checked", false);
            actionAdditionalBAPengembalianDanaDatatables(null, 0);
            
            if(response.data.length === 0){
                $("#modal_ba_pengembalian_dana_select").prop("disabled", true);
            }else{
                $("#modal_ba_pengembalian_dana_select").prop("disabled", false);
                response.data.forEach(item => {
                    $("#modal_ba_pengembalian_dana_select").append(`<option value="${item.noba}" date-value="${item.tglba}">${item.noba}</option>`);
                });
            }
            $("#modal_ba_pengembalian_dana").modal("show");
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

function actionCetakFormPengembalianBarang(){
    Swal.fire({
        title: 'Yakin?',
        html: `Cetak Form Pengembalian Barang?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/cetakFormPengembalianBarang`,
                type: "POST",
                xhrFields: {
                    responseType: 'blob' // Important for binary data
                },
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    var blob = new Blob([response], { type: 'application/pdf' }); // Corrected line
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'FORM-PENGEMBALIAN-BARANG.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
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
    });
}

function actionLaporanPenyusutanHarian(){
    Swal.fire({
        title: 'Yakin?',
        html: `Cetak Laporan Penyusutan Harian?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/LaporanPenyusutanHarian`,
                type: "POST",
                data: {tanggal_trans: $("#tanggal_trans").val()},
                success: function(response) {
                    $.ajax({
                        url: currentURL + `/action/LaporanPenyusutanHarian`,
                        type: "POST",
                        data: {tanggal_trans: $("#tanggal_trans").val()},
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
                        },
                    })
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
    });
}

function actionLaporanPesananExpired(){
    setDateNow("#date_awal_modal_periode_pesanan");
    setDateNow("#date_akhir_modal_periode_pesanan");
    $("#modal_periode_pesanan").modal("show");
}

function actionBuktiSerahTerimaKardus(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/BuktiSerahTerimaKardus`,
        type: "POST",
        data: {isShowDatatables: 1},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            var data = response.data;
            $("#select_history_stk").empty();

            actionAdditionalBuktiSerahTerimaKardusDatatables('firstQuery');

            data.cbSTK.forEach(item => {
                $("#select_history_stk").append(`<option value="${item.nostk}" date-value="${item.tglstk}">${item.nostk} - ${item.tglstk}</option>`);
            });
            $("#select_history_stk").prop("disabled", true);
            $("#modal_stk").modal("show");
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


//? ACTION THAT CORRESPONDING FOR ELEMENT DIRECTLY
$("#modal_ba_pengembalian_dana_checkbox").change(function(){
    var noba, isHistory;
    if($(this).val() == 1){
        //* isHIstory Checked
        $("#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table").attr("disabled", true);
        $("#modal_ba_pengembalian_dana_select").attr("disabled", false);
        if($("#modal_ba_pengembalian_dana_select option").length == 0){
            Swal.fire("Peringatan!", "Belum Ada History BA Pengembalian Dana SPI!", "warning");
            return;
        }
        if($("#modal_ba_pengembalian_dana_select").val() !== ""){
            noba = $("#modal_ba_pengembalian_dana_select").val();
            isHistory = 1;
        }
    } else {
        //* isHIstory Not Checked
        $("#modal_ba_pengembalian_dana_select").attr("disabled", true);
        noba = null;
        isHistory = 0;
    }

    actionAdditionalBAPengembalianDanaDatatables(noba, isHistory);
});

$("#cek_history_stk").change(function(){
    var history;
    if($(this).val() == 1){
        $("#tb_bukti_stk tbody tr td input.checkbox-stk").attr("disabled", true);
        $("#select_history_stk").attr("disabled", false);
        if($("#cek_history_stk option").length == 0){
            Swal.fire("Peringatan!", "Belum Ada History Serah Terima Kardus!", "warning");
            return;
        }
        history = $("#cek_history_stk").val();
        actionAdditionalBuktiSerahTerimaKardusDatatables(history);
    }else {
        actionAdditionalBuktiSerahTerimaKardusDatatables(0);
    }
});

$("#select_history_stk").change(function(){
    if($("#cek_history_stk").val() == 1){
        if($("#select_history_stk").val() !== ""){
            actionAdditionalBuktiSerahTerimaKardusDatatables($("#select_history_stk").val());
        }
    }
});

$("#modal_ba_pengembalian_dana_select").change(function(){
    if($("#modal_ba_pengembalian_dana_checkbox").val() == 1){
        if($("#modal_ba_pengembalian_dana_select").val() !== ""){
            actionAdditionalBAPengembalianDanaDatatables($("#modal_ba_pengembalian_dana_select").val(), 1);
            $("#modal_ba_pengembalian_dana_tb tbody tr td input.checkbox-table").prop("checked", true);
        }
    }
});

$("#pengiriman_modal_ekspedisi").change(function(){
    if($(this).val() == "TEAM DELIVERY IGR"){
        $("label#nama_ekspedisi_modal_ekspedisi").text("Nama Ekspedisi");
        $("label#jarak_modal_ekspedisi").text("Ongkos Kirim");
    } else {
        $("label#nama_ekspedisi_modal_ekspedisi").text("Jenis Kendaraan");
        $("label#jarak_modal_ekspedisi").text("Jarak");
    }
});

$("#modal_master_picking_group").change(function(){
    $("#modal_loading").modal("show");
    $("#modal_master_picking").attr("data-status", "true");
    setTimeout(function() {
        actionAdditionalMasterPickingHHLoadUser(false);
        actionAdditionalMasterPickingHHFilterRak(false);
        actionAdditionalMasterPickingHHLoadRakUser(false); //? Datatables
        actionAdditionalMasterPickingHHLoadRakAll(false); //? Datatables
        $('#modal_master_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
        $("#modal_master_picking").attr("data-status", "false");
        $("#modal_loading").modal("hide");
    }, 500)
});

$("#modal_master_picking_kode_rak").change(function(){
    if($("#modal_master_picking").attr("data-status") == "true"){
        return;
    }
    $("#modal_loading").modal("show");
    setTimeout(() => {
        actionAdditionalMasterPickingHHLoadRakAll(false); //? Datatables
        $('#modal_master_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
        $("#modal_loading").modal("hide");
    }, 500);
});

$("#modal_master_picking_users").change(function(){
    if($("#modal_master_picking").attr("data-status") == "true"){
        return;
    }
    $("#modal_loading").modal("show");
    setTimeout(() => {
        actionAdditionalMasterPickingHHLoadRakUser(false); //? Datatables
        $('#modal_master_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
        $("#modal_loading").modal("hide");
    }, 500);
});

$("#modal_master_group_picking_grup").change(function(){
    if($("#modal_master_group_picking").attr("data-status") == "true"){
        return;
    }
    $("#modal_loading").modal("show");
    setTimeout(() => {
        actionAdditionalMasterPickingGroupPicking(false); //? Datatables
        $('#modal_master_group_picking').closest('.dataTables_wrapper').find('.dataTables_scrollHeadInner').css('width', '100%');
        $("#modal_loading").modal("hide");
    }, 500);
})

$("#modal_master_picking_pick_all").change(function(){
    if($(this).val() == 1){
        $(".checkbox-master-picking-1").prop("checked", true);
    } else {
        $(".checkbox-master-picking-1").prop("checked", false);
    }
});

function checkboxMasterPickingOnChange(){
    var allChecked = $('.checkbox-master-picking-1').length === $('.checkbox-master-picking-1:checked').length;
    $('#modal_master_picking_pick_all').prop('checked', allChecked);
};

$('#modal_master_group_picking').on('hidden.bs.modal', function () {
    $("#modal_master_picking").removeClass("brightness-blur");
});

$('#modal_master_group_picking').on('shown.bs.modal', function () {
    $("#modal_master_picking").addClass("brightness-blur");
});

$('#modal_master_picking').on('shown.bs.modal', function () {
    $("#modal_master_picking").removeClass("brightness-blur");
});

//* APPROVAL
function getUserLevelNameApproval(UsrLvl){
    let str;
    if (UsrLvl === 1) {
        str = "MANAGER";
    } else if (UsrLvl === 2) {
        str = "SUPERVISOR";
    } else if (UsrLvl === 999) {
        return "OTP";
    } else if (UsrLvl === 990) {
        return "IC";
    } else if (UsrLvl === 991) {
        return '{{ session("flagSPI") }}' ? "Store Mgr./Jr.Mgr. SPI" : "Store Mgr./Jr.Mgr. Toko Igr";
    } else {
        str = "KASIR";
    }
    return str;
}

$('#modal_approval').on('hidden.bs.modal', function () {
    $("#userlevel_approval").val();
    $("#keterangan_approval").val();
    $("#label_username_approval").text();
    $(".modal.brightness-blur").removeClass("brightness-blur");
});

function actionAdditionalApproval(){
    Swal.fire({
        title: 'Lanjutkan Requirement Approval Level ?',
        html: `Pastikan Username & Password sudah terisi`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            if($("#username_approval").val() == "" || $("#password_approval").val() == ""){
                Swal.fire("Peringatan!", "Username atau Password Belum Diisi", "warning");
                return;
            }
            if($("#userlevel_approval").val() == 999){
                //! IRVAN | cURL ERROR Resolve Host
                (async () => {
                    var dataValue = await connectToWebService("http://fo.indogrosir.lan/OMIWebService/OMIWebService.asmx/GetOTP?" + "KodeToko=" + '{{ session("KODECABANG") }}' , "POST");
                    if (dataValue) {
                        console.log(dataValue);
                    }
                })();
            } else {
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/action-approve`,
                    type: "POST",
                    data: {userlevel: $("#userlevel_approval").val(), keterangan: $("#keterangan_approval").val(), username: $("#username_approval").val(), password: $("#password_approval").val()},
                    success: function(response) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        actionLanjutanApprovalBaRusakKemasan(response.data.username, response.data.namaUser);
                        $("#username_approval").val();
                        $("#password_approval").val();
                        $("#modal_approval").modal("hide")
                    }, error: function(jqXHR, textStatus, errorThrown) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        Swal.fire({
                            text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                                ? jqXHR.responseJSON.message
                                : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                            icon: "error"
                        });
                        $("#username_approval").val();
                        $("#password_approval").val();
                    }
                });
            }
        }
    });
}
