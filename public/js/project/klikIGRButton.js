let tb_periode_pesanan;

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

function actionAdditionalCetakSTK(){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/BuktiSerahTerimaKardus`,
        type: "POST",
        data: {isShowDatatables: false, ckHistory: $("#cek_history_stk").val(), select_history_stk: $("#select_history_stk").val(), tglStkHistory: $("#select_history_stk").find('option:selected').attr("date")},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            $("#modal_stk").modal("hide");
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

function actionAdditionalHitungUlang(){
    if($("#txt_nama_modal_ekspedisi").val() == ''){
        Swal.fire("Peringatan", "Nama Ekspedisi Belum Diinput!");
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
        data: {pengiriman: $("#pengiriman_modal_ekspedisi").val(), txtNama: $("txt_nama_modal_ekspedisi").val()},
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
                    $('td:eq(5)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-pengembalian" value="${data.ba}" name="ba-checkbox">`);
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
            Swal.fire("Success!", response.message, "success");
            actionGlobalDownloadPdf(response.data.nama_file);
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
                if(response.data.flagFree){
                    $("#img_gratis_ongkir_modal_ekspedisi").removeClass("d-none");
                } else {
                    $("#img_gratis_ongkir_modal_ekspedisi").addClass("d-none");
                }
                response.data.namaEkspedisi1.forEach(item => {
                    $("#nama_ekspedisi_modal_ekspedisi").append(`<option value="${item.eks_kodeekspedisi}">${item.eks_namaekspedisi}</option>`);
                });
                response.data.namaEkspedisi2.forEach(item => {
                    $("#nama_ekspedisi_modal_ekspedisi").append(`<option value="${item.id}">${item.title}</option>`);
                });
                $("#jarak_modal_ekspedisi").val(response.data.jarakKirim);
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

function actionDraftStruk(){
    var selectedRow = tb.row(".select-r").data();
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/DraftStruk`,
        type: "POST",
        data: { kode_web: selectedRow.kodeweb, tanggal_trans: $("#tanggal_trans").val(), selectedRow: selectedRow },
        success: function(response) {
            // setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
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
                $("#modal_ba_pengembalian_dana_select").append(`<option value="123" date-value="22-22-222">123</option>`);
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
        data: {isShowDatatables: true},
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);    
            var data = response.data;
            $("#tb_bukti_stk tbody").empty();
            $("#select_history_stk").empty();
            if(data.datatables.length < 1){
                $("#tb_bukti_stk tbody").append(`<tr>
                    <td colspan="4">Tidak Ada Data</td>
                </tr>`)
            } else {
                data.datatables.forEach(item => {
                    $("#tb_bukti_stk tbody").append(`<tr>
                        <td>${item.no_pb}</td>
                        <td>${item.tgl_pb}</td>
                        <td>${item.kode_member}</td>
                        <td>${item.cetak}</td>
                    </tr>`)
                });
            }
            data.cbSTK.forEach(item => {
                $("#select_history_stk").append(`<option value="${item.nostk}" date="${item.tglstk}">${item.nostk} - ${item.tglstk}</option>`);
            });
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