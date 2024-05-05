let selectedTable,
    selectedData  =  [],
    dataVoucher =[],
    dataNoseri =[],
   //  listPLU =[],
   //  listDataVoucher = [],
   //  listDataRak = [],
    search  =  false,
    page = 1,
    field = null,
    cabang = null;

$(document).ready(function(){
      $('.table-container').css('height', '400px'); // Adjust height as needed
      $('.table-container').css('overflow', 'auto');
      $(".voucher-label").hide();
      $(".voucher-count").hide();
      $(".print").hide();
      $(".picking").hide();
      $(".try").hide();
      $(".checked").hide();
     
      // $('.select2').select2({
      //    allowClear: false
      // }); 

       /**
        * selected voucher
        */
      $('#table_voucher tbody').on('click', 'tr', function () {
         
         $('#table_voucher tbody tr').removeClass('selected-row');

         $(this).addClass('selected-row');
         
         $(".print").hide();
         $(".picking").hide();
         $(".try").hide();
         selectedTable = $(this).find('td').map(function (data) {
               return $(this).text();
         }).get();
            if(selectedTable[1] == "Siap DSPB" ) 
            {
               //   show button print
               $(this).find('.print').toggle();
            }
            if(selectedTable[1] == "Selesai DSPB" ) 
            {
               //   show button transfer ulang
               // $(this).find('.try').toggle();

               $(this).find('.print').toggle();
            }

            $(this).find('.picking').toggle();
         // selectedValue(selectedTable[1]);

      });


       /**
        * search voucher
        */
     $('#search_data').on('input', function () {
            var searchText = $(this).val().toLowerCase();

            $('#table_voucher tbody tr').each(function () {
               var rowText = $(this).text().toLowerCase();

               $(this).toggle(rowText.includes(searchText));
            });
      });

       /**
        * selected picking
        */
      $('#table_picking tbody').on('click', 'tr', function () {
         
         $('#table_picking tbody tr').removeClass('selected-row');

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
         //    //   show button print
         //    // $(this).find('.try').toggle();

         //    $(this).find('.print').toggle();
         // }
         // selectedValue(selectedTable[1]);

      });


       /**
        * search picking
        */
     $('#search_data').on('input', function () {
            var searchText = $(this).val().toLowerCase();

            $('#table_picking tbody tr').each(function () {
               var rowText = $(this).text().toLowerCase();

               $(this).toggle(rowText.includes(searchText));
            });
      });
      
      getVoucherData();
     

});


  
getVoucherData =(tanggal = null,report_qrcode = null)=>{
   let select = "",
       param = tanggal?"tanggal="+tanggal:'';
       param += report_qrcode?"&eport_qrcode="+report_qrcode:'';
       listDataVoucher = [];
       field = '';
   
   $("#table-content-voucher").html(field);
   $('#table_voucher tbody tr').removeClass('selected-row');
   $(".voucher-label").hide();
   $(".voucher-count").hide();
   $('#label-tag').loading('toggle');
   $.getJSON(link + "/api/voucher/data?"+param, function(data) {
     
       // list data voucher
       
      // if(data.data){
         $.each(data.data.list_data,function(key,value) {
           
            field+=`
                     <tr>

                           <td>${value.no?value.no:'-'}
                              <button class="btn btn-sm btn-primary print" id="print-button" onclick="print_laporan('${value.nopb}','${value.kodetoko}','${value.tglpb}')">
                                    <i class="fas fa-print"></i> Print
                              </button>
                              <button class="btn btn-sm btn-primary try" id="print-button" onclick="print_laporan(${value.nopb})">
                                   Transfer Ulang
                              </button>
                              <button type="button" class="btn btn-sm btn-primary picking" data-toggle="modal" data-target="#modal_picking" onclick="picking_load('${value.kodetoko}' , '${value.nopb}' , '${value.tglpb}')">Picking</button>
                           </td>
                           <td>${value.stat?value.stat:'-'}</td>
                           <td>${value.kodetoko?value.kodetoko:'-'}</td>
                           <td>${value.nopb?value.nopb:'-'}</td>
                           <td>${value.tglpb?value.tglpb:'-'}</td>
                     </tr>
                  `;
                  dataVoucher[value.kodetoko] = value;
         });

         
         $("#table-content-voucher").append(field);
         $(".print").hide();
         $(".picking").hide();
         $(".try").hide();
         $(".voucher-label").show();
         $(".voucher-count").show();
         $(".voucher-label").html(data.data.label);
         $(".voucher-count").html(data.data.count);

         $(".pb_total").html(data.data.pb_total);
         $(".siap_picking").html(data.data.siap_picking);
         $(".siap_dspb").html(data.data.siap_dspb);
         $(".selesai_dspb").html(data.data.selesai_dspb);

      // }

   }).fail(function() {
      $('#label-tag').loading('toggle');
   }).done(function() {
      $('#label-tag').loading('toggle');
   }); 


}

changeDate=(date)=>{
   let tanggal = date.value;
   getVoucherData(tanggal);
}
picking_load=(kodetoko =null , nopb =null , tglpb =null)=>{
   let param = '';
       param += kodetoko?"kodetoko"+kodetoko:"";
       param += nopb?"&nopb="+nopb:"";
       param += tglpb?"&tglpb="+tglpb:"";

   $('#picking_label').html(kodetoko+' / '+nopb+' / '+tglpb);
   $('#modal_picking').loading('toggle');
   $.getJSON(link + "/api/voucher/picking?"+param, function(data) {
      $("#plu_picking").val(data.data.plu_picking);
      $("#deskripsi_picking").val(data.data.deskripsi_picking);
      $("#qty_order_picking").val(data.data.qty_order_picking);
      $("#qty_realisasi_picking").val(data.data.qty_realisasi_picking);
      $("#no_picking1").val(data.data.no_picking1);
      $("#no_picking2").val(data.data.no_picking2);
      $("#kodetoko").val(kodetoko);
      $("#nopb").val(nopb);
      $("#tglpb").val(tglpb);
      // list data no ref
      
     // if(data.data){
        $.each(data.data.noseri,function(key,value) {
          
           field+=`
                    <tr>

                          <td>${value.noseri?value.noseri:'-'}
                          </td>
                    </tr>
                 `;
                 dataNoseri[value.noseri] = value;
        });

     // }

  }).fail(function(data) {
     $('#modal_picking').loading('toggle');
   //   if (data.responseJSON.errors) {
   //       Swal.fire({
   //          title: data.responseJSON.messages,
   //          html: '',
   //          icon: 'warning',
   //          allowOutsideClick: false,
   //          onOpen: () => {
   //                   swal.hideLoading()
   //          }
   //       });
   //       $('#modal_picking').modal('hide')
         
   //    }
   }).done(function() {
     $('#modal_picking').loading('toggle');
  }); 
}

print_laporan =(nopb = null, kodetoko = null, tglpb = null)=>{
   console.log(nopb,kodetoko,tglpb)
   if ($('#report_qr').prop('checked')) {
      window.open(link+"/api/voucher/printqr?nopb="+nopb+"&kodetoko="+kodetoko+"&tglpb="+tglpb,'_blank');
  } 
   window.open(link+"/api/voucher/printreport?nopb="+nopb+"&kodetoko="+kodetoko+"&tglpb="+tglpb,'_blank');
}

save_picking =()=>{
   let  plu_picking = $("#plu_picking").val(),
      deskripsi_picking = $("#deskripsi_picking").html(),
      qty_order_picking = $("#qty_order_picking").val(),
      qty_realisasi_picking = $("#qty_realisasi_picking").val(),
      no_picking1 = $("#no_picking1").val(),
      no_picking2 = $("#no_picking2").val(),
      tglpb =$("#tglpb").val() ,
      nopb =$("#nopb").val() ,
      kodetoko =$("#kodetoko").val() ;

   let csrf = $('meta[name="csrf-token"]').attr('content'),
      plu = [];
      omi = $("#omi").val();
      formPicking = new FormData();
      listPLUEdit.forEach(function(index){
        plu.push(dataPlu[index])
     })
  formPicking.append('_token', csrf);
  formPicking.append("plu_picking",plu_picking);
  formPicking.append("deskripsi_picking",deskripsi_picking);
  formPicking.append("qty_order_picking",qty_order_picking);
  formPicking.append("qty_realisasi_picking",qty_realisasi_picking);
  formPicking.append("no_picking1",no_picking1);
  formPicking.append("no_picking2",no_picking2);
  formPicking.append("tglpb",tglpb);
  formPicking.append("nopb",nopb);
  formPicking.append('kode_toko', kode_toko);
  formPicking.append('noseri', JSON.stringify(dataNoseri));
 
   $('#modal_picking').loading('toggle');
   $.ajax({
         url: link+'/api/voucher/picking/save',
         method: 'POST',
         data: formPicking,

         success: function (response) {
         },
         error: function (xhr) {
         },
         cache: false,
         contentType: false,
         processData: false,
         dataType: "json"
   }).done(function () {

      $('#modal_picking').loading('toggle');
   });  
}

