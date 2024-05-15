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

function actionAdditionaCekPaymentChangeStatus(){
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
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                var blob = new Blob([data], { type: 'application/zip' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'DRAFT-STRUK.zip';
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
            Swal.fire("Success", "Pembayaran Terkonfirmasi", "success");
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
        data: { no_trans: selectedRow.no_trans, status: selectedRow.status, nopb: selectedRow.no_pb, tipe_bayar: selectedRow.tipe_bayar, tanggal_pb: selectedRow.tgl_pb, kode_web: selectedRow.kodeweb, kode_member: selectedRow.kode_member, tipe_kredit: selectedRow.tipe_kredit, tanggal_trans: $("#tanggal_trans").val() },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire("Success", "Pembayaran Terkonfirmasi", "success");
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
    var selectedRow = tb.row(".select-r").data();
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
                // data: {nopb: selectedRow.no_pb, no_trans: selectedRow.no_trans, kode_member: selectedRow.kode_member, tanggal_trans: $("#tanggal_trans").val()},
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
$("#pengiriman_modal_ekspedisi").change(function(){
    if($(this).val() == "TEAM DELIVERY IGR"){
        $("label#nama_ekspedisi_modal_ekspedisi").text("Nama Ekspedisi");
        $("label#jarak_modal_ekspedisi").text("Ongkos Kirim");
    } else {
        $("label#nama_ekspedisi_modal_ekspedisi").text("Jenis Kendaraan");
        $("label#jarak_modal_ekspedisi").text("Jarak");
    }
});