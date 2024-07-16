let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    listToko =[],
    dataToko =[],
    dataInput =[],
    search  =  false,
    page = 1,
    dpp_idm = 0,
    ppn_idm = 0,
    total_idm = 0,
    dpp_igr = 0,
    ppn_igr = 0,
    total_igr = 0,
    retur_fisik = 0,
    retur_peforma = 0,
    selisih = 0,
    field = null,
    dtKey = [],
    formData = null,
    cabang = null;

$(document).ready(function(){
      /**
       * table_wt
       */
      $('#table_proseswt input[type="checkbox"]').click(function () {
            // Toggle the 'selected' class on the parent row
            $(this).closest('tr').toggleClass('selected-row', this.checked);
      });
      $('#table_proseswt tbody').on('click', 'tr', function () {

         $(this).toggleClass('selected-row');
         selectedTable = $(this).find('td').map(function (data) {
               return $(this).text();
         }).get();
         $(this).find('input[type="checkbox"]').prop('checked', function (i, oldProp) {
            
            if ($(this).is(':checked')) {
               addDataFile(selectedTable[1],false)
               $(this).addClass('selected-row');
            } else {
               $(this).removeClass('selected-row');
               addDataFile(selectedTable[1],true)
            }
            return !oldProp;
         });
         

      });
      
     

      // $('.select2').select2({
      //    allowClear: false
      // }); 
      $('#form_wt').submit(function(e){
         e.preventDefault();
           formData = new FormData(this)
      })
});


addDataFile=(plu,status)=>{
   if (status) {
      if (listToko.indexOf(plu) === -1) {
         listToko.push(plu)
     }

     click_table()
      
   } else {
      // remove array by value
      Array.prototype.remove = function() {
         var what, a = arguments, L = a.length, ax;
         while (L && this.length) {
             what = a[--L];
             while ((ax = this.indexOf(what)) !== -1) {
                 this.splice(ax, 1);
             }
         }
         return this;
     };
      listToko.remove(plu)

      $(".checkbox-all").prop('checked',false)
   }
}

click_table=()=>{
   console.log(selectedTable)
   let formDataTable = new FormData(); 
      formDataTable.append('kodetoko', selectedTable[1]);
      formDataTable.append('nama_toko', selectedTable[2]);
      formDataTable.append('hari_bulan', selectedTable[3]);
      formDataTable.append('file_wt', selectedTable[4]);
   $('#proses-wt').loading('toggle');
   $.ajax({
         url: link+'/api/proseswt/read',
         method: 'POST',
         data: formDataTable,

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
      

        let inputData = data.data;
            dpp_idm += inputData.dppIdm;
            ppn_idm += inputData.ppnIdm;
            total_idm += inputData.dppIdm+inputData.ppnIdm;
            dpp_igr += inputData.dppIgr;
            ppn_igr += inputData.ppnIgr;
            total_igr += inputData.dppIgr+inputData.ppnIgr;
            retur_fisik += inputData.returFisik;
            retur_peforma += inputData.returPerforma;
            dtKey.push(inputData.dtKey);

         $('.dpp_idm').val(format_currency(dpp_idm));
         $('.ppn_idm').val(format_currency(ppn_idm));
         $('.total_idm').val(format_currency(total_idm));
         $('.dpp_igr').val(format_currency(dpp_igr));
         $('.ppn_igr').val(format_currency(ppn_igr));
         $('.total_igr').val(format_currency(total_igr));
         $('.retur_fisik').val(format_currency(retur_fisik));
         $('.retur_peforma').val(format_currency(retur_peforma));
   
       selisih += total_idm - total_igr;
      $('.total_selisih').html(format_currency(selisih));
      $('#proses-wt').loading('toggle');
   }); 

  

}

submit_wt =()=>{

   $('#proses-wt').loading('toggle');
   $('#form_wt').submit()

   $("#table-content-proseswt").html('');

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
                        <tr class="row-checkbox">
   
                              <td><input type="checkbox" value="1" class="colm-checkbox">${no++}</td>
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
       formData.append('dtKey', btoa(JSON.stringify(dtKey)));
       formData.append('namafile', selectedTable[4]);
       formData.append('ppn_idm',back_to_integer(ppn_idm));
       formData.append('dpp_idm',back_to_integer(dpp_idm));
       formData.append('total_idm',back_to_integer(total_idm));
       formData.append('dpp_igr',back_to_integer(dpp_igr));
       formData.append('ppn_igr',back_to_integer(ppn_igr));
       formData.append('total_igr',back_to_integer(total_igr));
       formData.append('retur_fisik',back_to_integer(retur_fisik));
       formData.append('retur_peforma',back_to_integer(retur_peforma));
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
                  download_txt(response.data_struk)
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

download_txt=(data)=>{
   // $('#downloadBtn').click(function(){
      var textData = data,
          no = 1;
      
      // Create a blob with the text data
      var blob = new Blob([textData], { type: 'text/plain;charset=utf-8' });
      
      // Create a temporary anchor element
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(blob);
      
      // Set the href attribute of the anchor element to the Blob URL
      a.href = url;
      
      // Set the download attribute to specify the filename
      a.download = 'LISTING ITEM SO.txt';
      
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
back_to_integer = (input) =>{
   let results = input.replace(/\,/g,'');
   return parseInt(results);
}

