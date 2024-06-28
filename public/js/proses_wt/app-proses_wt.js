let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    dataToko =[],
    dataInput =[],
    search  =  false,
    page = 1,
    field = null,
    formData = null,
    cabang = null;

$(document).ready(function(){
      /**
       * table_wt
       */
      
      $('#table_proseswt tbody').on('click', 'tr', function () {

         $(this).toggleClass('selected-row');
         selectedTable = $(this).find('td').map(function (data) {
               return $(this).text();
         }).get();
         

      });

      // $('.select2').select2({
      //    allowClear: false
      // }); 
      $('#form_wt').submit(function(e){
         e.preventDefault();
           formData = new FormData(this)
      })
});

click_table=(toko = null)=>{

   $('#proses-wt').loading('toggle');
  let inputData = dataInput[toko][0];
   $('.dpp_idm').val(format_currency(inputData.dpp_idm));
   $('.ppn_idm').val(format_currency(inputData.ppn_idm));
   $('.total_idm').val(format_currency(inputData.total_idm));
   $('.dpp_igr').val(format_currency(inputData.dpp_igr));
   $('.ppn_igr').val(format_currency(inputData.ppn_igr));
   $('.total_igr').val(format_currency(inputData.total_igr));
   $('.retur_fisik').val(format_currency(inputData.retur_fisik));
   $('.retur_peforma').val(format_currency(inputData.retur_performa));

var selisih = inputData.total_idm - inputData.total_igr;
$('.total_selisih').html(format_currency(selisih));

$('#proses-wt').loading('toggle');
  

}

submit_wt =()=>{

   $('#proses-wt').loading('toggle');
   $('#form_wt').submit()

   $("#table-content-proseswt").html('');
   // let csrf = $('meta[name="csrf-token"]').attr('content'),
   //       file = $("#file").val();
   //       formWT = new FormData();
   //    formWT.append('_token', csrf);
   //    formWT.append("file",file);

      $.ajax({
            url: link+'/api/proseswt/send',
            method: 'POST',
            data: formData,

            success: function (response) {
            },
            error: function (xhr) {
            },
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json"
      }).fail(function (e) {
            Swal.fire({
               title: 'Gagal',
               html: 'Data kosong',
               icon: 'warning',
               allowOutsideClick: false,
               onOpen: () => {
                     swal.hideLoading()
               }
         });
         $('#proses-wt').loading('toggle');
      }).done(function (data) {
         
         if (data.data.data_toko) {
         let no = 1;
            field = null;

            $.each(data.data.data_toko,function(key,value) {
               field+=`
                        <tr onclick="click_table('${value.toko}')">
   
                              <td>
                                 ${no++}
                              </td>
                              <td>${value.toko?value.toko:'-'}</td>
                              <td>${value.nama_toko?value.nama_toko:'-'}</td>
                              <td>${value.hari_bln?value.hari_bln:'-'}</td>
                              <td>${value.file_wt?value.file_wt:'-'}</td>
                        </tr>
                     `;
                     dataToko[value.toko] = value;
            });
            dataInput = data.data.data_input;
   
            
            $("#table-content-proseswt").append(field);
            // $('.dpp_idm').val(format_currency(data.data.dpp_idm));
            // $('.ppn_idm').val(format_currency(data.data.ppn_idm));
            // $('.total_idm').val(format_currency(data.data.total_idm));
            // $('.dpp_igr').val(format_currency(data.data.dpp_igr));
            // $('.ppn_igr').val(format_currency(data.data.ppn_igr));
            // $('.total_igr').val(format_currency(data.data.total_igr));
            // $('.retur_fisik').val(format_currency(data.data.retur_fisik));
            // $('.retur_peforma').val(format_currency(data.data.retur_performa));

            // var selisih = data.data.total_idm - data.data.total_igr;
            // $('.total_selisih').html(format_currency(selisih));
         
           
         }else{
            Swal.fire({
               title: 'Gagal',
               html: 'Harap Periksa kembali File Anda',
               icon: 'warning',
               allowOutsideClick: false,
               onOpen: () => {
                       swal.hideLoading()
               }
           });
         }
         $('#proses-wt').loading('toggle');
      }); 

}

proses_wt=()=>{
   let formData = new FormData(),
       file = $('#file').val(),
       csrf = $('meta[name="csrf-token"]').attr('content'),
       dpp_idm = $('.dpp_idm').val(),
       ppn_idm = $('.ppn_idm').val(),
       total_idm = $('.total_idm').val(),
       dpp_igr = $('.dpp_igr').val(),
       ppn_igr = $('.ppn_igr').val(),
       total_igr = $('.total_igr').val(),
       retur_fisik = $('.retur_fisik').val(),
       retur_peforma = $('.retur_peforma').val();
       formData.append('_token', csrf);
       formData.append('file', file);
       formData.append('dpp_idm',dpp_idm);
       formData.append('ppn_idm',ppn_idm);
       formData.append('total_idm',total_idm);
       formData.append('dpp_igr',dpp_igr);
       formData.append('ppn_igr',ppn_igr);
       formData.append('total_igr',total_igr);
       formData.append('retur_fisik',retur_fisik);
       formData.append('retur_peforma',retur_peforma);
       
   Swal.fire({
      title: "Anda Yakin melakukan Proses WT?",
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: `Ya`,
      denyButtonText: `Tidak`,
  }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.value) {

          $('proses-wt').loading('toggle');
          load = 1;
          Swal.fire({
              title: 'Loading..',
              html: '',
              allowOutsideClick: false,
              onOpen: () => {
                      swal.showLoading()
              }
          });
          $.ajax({
              url: link+'/api/proseswt/proseswt',
              method: 'POST',
              data: formData,
  
              success: function (response) {

                  let messages = response.messages?response.messages:'Data Berhasil Diproses';
                  Swal.fire(
                      'Berhasil',
                      messages,
                      'success'
                  ) 
                  $('proses-wt').loading('toggle');
              
              },
              error: function (xhr) {

                  
                  $('proses-wt').loading('toggle');
                  let res = xhr.responseJSON,
                      messages = xhr.responseJSON.messages?xhr.responseJSON.messages:'Harap Periksa kembali data yang anda input';
                      
                  Swal.fire({
                      title: 'Gagal',
                      html: messages,
                      icon: 'warning',
                      allowOutsideClick: false,
                      onOpen: () => {
                              swal.hideLoading()
                      }
                  });
                  if ($.isEmptyObject(res) == false) {
                      $.each(res.errors, function (i, value) {
                          $('#' + i).addClass('is-invalid');
                          $('.' + i).append('<span class="help-block"><strong>' + value + '</strong></span>')
                      })
                  }
              },
              cache: false,
              contentType: false,
              processData: false,
              dataType: "json"
          });  
      }
      /* Read more about isConfirmed, isDenied below */
  });
}


strCenter=(str, width)=>{
   const pad = Math.max(0, width - str.length) / 2;
   return ' '.repeat(Math.floor(pad)) + str + ' '.repeat(Math.ceil(pad));
 }
strPad=(str, width, char = " ", left = false)=>{
   str = String(str)
   if (left) {
     return str.padStart(width, char);
   }
   return str.padEnd(width, char);
 }

download_txt=()=>{
   // $('#downloadBtn').click(function(){
      var textData = '',
          no = 1;
      textData += "=====================================================================================================\n";
      textData += strCenter("LISTING ITEM SO", 101) + "\n";
      textData += "=====================================================================================================\n";
      textData += strPad(`ID         : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`CAB        : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`STT        : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`NO.TR      : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += "=====================================================================================================\n";
      textData += ' NO. NAMA BARANG                            PLU       QTY    H.SATUAN    DISC.            TOTAL ' + "\n";
      textData += "=====================================================================================================\n";
      $.each(dataPrint,function(key,row) {
         const qtyCTN = Math.floor(row.lso_qty / row.prd_frac);
         const qtyPCS = row.lso_qty % row.prd_frac;

         textData += strPad(no++, 3, " ", true) + "   " + strPad(row.prd_deskripsipendek, 22) + "(" + strPad(row.prd_prdcd, 7) + ")   \n";
         textData += strPad(row.prd_unit, 14, " ", true) + " / " + strPad(Number(row.prd_frac).toFixed(0), 8) + strPad(Number(qtyCTN).toFixed(0), 5, " ", true) + strPad(Number(qtyPCS).toFixed(0), 8, " ", true) + "  \n";
      });
      textData += "=====================================================================================================\n";
      textData += strPad(`Total Item        : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`Total (+ PPN)     : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`Pembayaran Kredit : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`Total Pembayaran  : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`Member            : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`No Koli           : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      textData += strPad(`Selesai           : ${lokasiPrint?lokasiPrint:'-'}`, 101) + "\n";
      
      // Create a blob with the text data
      var blob = new Blob([textData], { type: 'text/plain;charset=utf-8' });
      
      // Create a temporary anchor element
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(blob);
      
      // Set the href attribute of the anchor element to the Blob URL
      a.href = url;
      
      // Set the download attribute to specify the filename
      a.download = 'LISTING ITEM SO '+waktuPrint+'.txt';
      
      // Programmatically click the anchor element to trigger the download
      a.click();
      
      // Release the Object URL resource
      window.URL.revokeObjectURL(url);
   //  });
}

format_currency=(data)=>{
   var value = data.toLocaleString();
   n = parseInt(value.replace(/\D/g, ''), 10);
   return n.toLocaleString();

}