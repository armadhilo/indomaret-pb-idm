let selectedTable,
    selectedData  =  [],
    dataPLU =[],
    listPLU =[],
    listDataPLU = [],
    listDataRak = [],
    search  =  false,
    page = 1,
    field = null,
    cabang = null;

$(document).ready(function(){
       /**
        * selected voucher
        */
       $('#table_pengiriman tbody').on('click', 'tr', function () {
         
         $('#table_pengiriman tbody tr').removeClass('selected-row');

         $(this).addClass('selected-row');
         
         $(".print").hide();
         $(".picking").hide();
         $(".try").hide();
         selectedTable = $(this).find('td').map(function (data) {
               return $(this).text();
         }).get();
            // if(selectedTable[1] == "Siap DSPB" ) 
            // {
            //    //   show button print
            //    $(this).find('.print').toggle();
            // }
            // if(selectedTable[1] == "Selesai DSPB" ) 
            // {
            //    //   show button transfer ulang
            //    // $(this).find('.try').toggle();

            //    $(this).find('.print').toggle();
            // }

            // $(this).find('.picking').toggle();
         // selectedValue(selectedTable[1]);

      });


       /**
        * search voucher
        */
     $('#search_pengiriman').on('input', function () {
            var searchText = $(this).val().toLowerCase();

            $('#table_pengiriman tbody tr').each(function () {
               var rowText = $(this).text().toLowerCase();

               $(this).toggle(rowText.includes(searchText));
            });
      });

      $('.select2').select2({
         allowClear: false
      }); 
      getDataMonitoring();

      $(".monitoring-label").hide();
});

list_kubikasi_pb_idm =()=>{
   var getTanggal = $(".tanggal").val(),
       newDate;
   if (getTanggal !== '') {
   var dateAr =  getTanggal.split('-');
         newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
   }

   var tanggal = newDate?newDate:moment().format('DD-MM-YYYY');
   let text = 'Cetak List Kubikasi Pb Idm tanggal '+tanggal+' ?';

   Swal.fire({
      title: text,
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: `Ya`,
      denyButtonText: `Tidak`,
  }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.value) {
          load = 1;
          Swal.fire(
                  'Berhasil',
                  'success'
            )
          window.open(link+'/api/monitoring/download/list_kubikasi_pb_idm?tanggal='+tanggal,'_blank');
         
      }
      /* Read more about isConfirmed, isDenied below */
  });
}
cetak_data_paket_pengiriman_idm =()=>{
   var getTanggal = $(".tanggal").val(),
       newDate;
   if (getTanggal !== '') {
   var dateAr =  getTanggal.split('-');
         newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
   }

   var tanggal = newDate?newDate:moment().format('DD-MM-YYYY');
   let text = 'Cetak List Paket Pengiriman Pb Idm tanggal '+tanggal+' ?';

   Swal.fire({
      title: text,
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: `Ya`,
      denyButtonText: `Tidak`,
  }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.value) {
          load = 1;
          Swal.fire(
                  'Berhasil',
                  'success'
            )
          window.open(link+'/api/monitoring/download/list_paket_pengiriman_idm?tanggal='+tanggal,'_blank');
         
      }
      /* Read more about isConfirmed, isDenied below */
  });
}
file_rekon =()=>{
   var getTanggal = $(".tanggal").val(),
       newDate;
   if (getTanggal !== '') {
      var dateAr =  getTanggal.split('-');
          newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
   }

   var tanggal = newDate?newDate:moment().format('DD-MM-YYYY');
   let text = 'Create File CSV AMS tanggal '+tanggal+' ?';

   Swal.fire({
      title: text,
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: `Ya`,
      denyButtonText: `Tidak`,
  }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.value) {
          load = 1;
          Swal.fire(
                  'Berhasil',
                  'success'
            )
          window.open(link+'/api/monitoring/download/filerekon?tanggal='+tanggal,'_blank');
         
      }
      /* Read more about isConfirmed, isDenied below */
  });
}

download_txt=()=>{
   // $('#downloadBtn').click(function(){
      var textData = '';
      textData += "ISI KOLI\n";
      textData += "TOKO  : T7G5 - MODERN BOULEVARD\n";
      textData += "NO PB : 901064 / 23-03-2024\n\n";
      textData += "No PICK : 63437\n";
      textData += "No KOLI : 020063437001\n";
      textData += "=================================================================================================\n";
      textData += "  NO. NAMA BARANG                            PLU       QTY    H.SATUAN    DISC.            TOTAL \n";
      textData += "=================================================================================================`\n";
      textData += "   1 POP MIE AYAM 75G      0037141    24\n";
      textData += "   2 SOSRO TEH BTL TPK 1L  1141511    12\n";
      textData += "   3 SARIMI GOR.AYM KK125  1179331    24\n";
      textData += "   4 QTELA KRPK BLDO 180G  1254071    12\n";
      textData += "========================================\n";
      textData += "TOTAL : 4 ITEM / RP + PPN  :   433.536\n";
      
      // Create a blob with the text data
      var blob = new Blob([textData], { type: 'text/plain;charset=utf-8' });
      
      // Create a temporary anchor element
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(blob);
      
      // Set the href attribute of the anchor element to the Blob URL
      a.href = url;
      
      // Set the download attribute to specify the filename
      a.download = 'text_file.txt';
      
      // Programmatically click the anchor element to trigger the download
      a.click();
      
      // Release the Object URL resource
      window.URL.revokeObjectURL(url);
   //  });
}

toggleInput =(nameClass,deleteVar)=>{

   $('#label-tag').loading('toggle');
   let className = '.'+nameClass;
   
   $('.input-form').hide();
   $('.input-data').prop('disabled',true);
   $(className).prop('disabled',false);
   $(className).show();

   $('#label-tag').loading('toggle');

}


getDataMonitoring =(tanggal = null,zona = null,report_qrcode = null,report_zona = null)=>{
   let select = "";
       listDataPLU = [];
       param = tanggal?"tanggal="+tanggal:'';
       param += report_zona?"&report_zona="+report_zona:'';
       param += zona?"&zona="+zona:'';
       param += report_qrcode?"&report_qrcode="+report_qrcode:'';
     
   $('#label-tag').loading('toggle');
   $.getJSON(link + "/api/monitoring/data?"+param, function(data) {
     
      if(data){
         $.each(data.data_zona,function(key,value){
               select+=` <option value="${value.zon_kode}" >${value.zon_kode}</option>`;
               listDataPLU[value.prdcd] = value;

         });
         $(".siapDspb").html(data.data_card.siapDspb)
         $(".jmlhPb").html(data.data_card.jmlhPb)
         $(".sendJalur").html(data.data_card.sendJalur)
         $(".picking").html(data.data_card.picking)
         $(".scanning").html(data.data_card.scanning)
         $(".selesaiLoading").html(data.data_card.selesaiLoading)
         $(".selesaiDspb").html(data.data_card.selesaiDspb)
         $(".monitoring-label").show();
         $(".monitoring-label").html(data.data_card.lblMonitoring)
         $("#zona").append(select);
      }

   }).fail(function() {
      $('#label-tag').loading('toggle');
   }).done(function() {
      $('#label-tag').loading('toggle');
   });

   // $('#label-tag').loading('toggle');

}
getDataPaketPengirimanIDM =()=>{
   let select = "";
       listDataPLU = [];
       var getTanggal = $(".tanggal").val(),
            newDate;
         if (getTanggal !== '') {
         var dateAr =  getTanggal.split('-');
               newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
         }

         var tanggal = newDate?newDate:moment().format('DD-MM-YYYY');
       param = tanggal?"tanggal="+tanggal:'';
     
   $('#modal_pengiriman').loading('toggle');
   $.getJSON(link + "/api/monitoring/data/list_paket_pengiriman_idm?"+param, function(data) {
      field = '';
      $('#table-content-pengiriman').html('');
      if(data.data.length > 0){
         $.each(data.data,function(key,value){
            field+=`
            <tr>
                  <td>${value.no_pengiriman?value.no_pengiriman:'-'}</td>
                  <td>${value.kode_toko?value.kode_toko:'-'}</td>
                  <td>${value.no_pb?value.no_pb:'-'}</td>
                  <td>${value.tgl_pb?value.tgl_pb:'-'}</td>
                  <td>${value.no_dspb?value.no_dspb:'-'}</td>
            </tr>
         `;

         });

      }else{
         Swal.fire({
            title: 'Gagal',
            html: 'Data kosong',
            icon: 'warning',
            allowOutsideClick: false,
            onOpen: () => {
                    swal.hideLoading()
            }
        });
         $('#modal_pengiriman').modal('hide');
      }

   }).fail(function() {
      $('#modal_pengiriman').loading('toggle');
   }).done(function() {
      console.log(field !== '')
      if (field !== '') {
         $('#table-content-pengiriman').append(field);
      }
      $('#modal_pengiriman').loading('toggle');
   });

   // $('#label-tag').loading('toggle');

}
