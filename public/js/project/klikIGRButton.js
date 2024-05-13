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
        data: {pengiriman: $("#pengiriman_modal_ekspedisi").val()},
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
        $('input[name="input_jalur_picking"]:checked').val()
        $("#modal_pilih_jalur_picking").modal("hide");
        $.ajax({
            url: currentURL + `/action/SendHandHelt`,
            type: "POST",
            data: {no_trans: selectedRow.no_trans, status: selectedRow.status, statusSiapPicking: statusSiapPicking, pilihan: $('input[name="input_jalur_picking"]:checked').val(), nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member, tanggal_trans: $("#tanggal_trans").val(), pickRakToko: $("#pick_rak_toko").val()},
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
    } else {
        Swal.fire({
            title: 'Yakin?',
            text: "Send Jalur No Trans " + selectedRow.no_trans + " ini ?",
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                // if(statusSiapPicking !== selectedRow.status){
                //     Swal.fire("Peringatan!", "Bukan Data Yang Siap Send Jalur!", "warning");
                //     return;
                // }
                // if($("#tanggal_trans").val() == ''){
                //     Swal.fire("Peringatan!", "Pilih Tanggal Trans Terlebih Dahulu", "warning");
                //     return;
                // }
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