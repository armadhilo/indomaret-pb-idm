let selectedTable  =  [],
    selectedData  =  [],
    selectedToko =[],
    dataRetur =[],
    dataNRB =[],
    dataKoli =[],
    listToko =[],
    search  =  false,
    page = 1,
    field = null,
    fieldKoli = null,
    formData = null,
    RECID_S = false,
    NonF_condition = false,
    F_condition = false,
    cabang = null;

$(document).ready(function(){


      /**
       * table_retur
       */
         $('#table_retur tbody').on('click', 'tr', function () {
            $(this).find('.editable').on('click', function() {
               var $this = $(this);
               if ($this.find('input').length === 0) {
                   var currentText = $this.text();
                   var $input = $('<input>', {
                       type: 'text',
                       value: currentText,
                       class: 'small-input',
                       blur: function() {
                           var newText = $(this).val();
                           $this.text(newText);
                       },
                       keyup: function(e) {
                           if (e.which === 13) {
                               $(this).blur();
                           }
                       }
                   }).appendTo($this.empty()).focus();
               }
           });
            
            $('#table_retur tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');

            selectedTable = $(this).find('td').map(function (data) {
                  return $(this).text();
            }).get();
            

         });
      /**
      /**
       * table_nrb
       */
         $('#table_NRB tbody').on('click', 'tr', function () {
            
            $('#table_NRB tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');

            selectedTable = $(this).find('td').map(function (data) {
                  return $(this).text();
            }).get();
            dataRetur();

         });
      /**
       * table_koli
       */
         $('#table_koli tbody').on('click', 'tr', function () {
            
            $('#table_koli tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');

            selectedTable = $(this).find('td').map(function (data) {
                  return $(this).text();
            }).get();

         });
         

      // });

      $('.select2').select2({
         allowClear: false
      }); 
      get_toko()
      $('#form_wt').submit(function(e){
         e.preventDefault();
           formData = new FormData(this)
      })
      $('.NonF').hide()
      $('.F').hide()
});

get_toko=()=>{

      $('#retur_card').loading('toggle');
      let select = '';
      $.getJSON(link + "/api/retur/data/toko", function(data) {
      
         if(data){
            $.each(data,function(key,value){
                  select+=` <option value="${value.tko_kodecustomer}" >${value.tko_kodeomi}-${value.tko_kodecustomer}</option>`;
                  listToko[value.tko_kodeomi] = value;

            });
            $("#toko").append(select);
         }
      }).fail(function() {
         $('#retur_card').loading('toggle');
      }).done(function() {
         $('#retur_card').loading('toggle');
      })
}

get_data_nrb =()=>{
   let select = "",
       toko = $("#toko").val();
      param = toko?"toko="+toko:'';
      field = '';
   $('#retur_card').loading('toggle');
   $.getJSON(link + "/api/retur/data/nrb?"+param, function(data) {
     
       
      // if(data.data){
         $.each(data.data,function(key,value) {
            field+=`
                     <tr>
                           <td>${value.docno?value.docno:'-'}</td>
                           <td>${value.tgl1?value.tgl1:'-'}</td>
                           <td>${value.type?value.type:'-'}</td>
                     </tr>
                  `;
                  dataNRB[value.docno] = value;
         });

         
         $("#table-content-nrb").append(field);

      // }

   }).fail(function(data) {
      Swal.fire({
         title: data.responseJSON.messages,
         html: '',
         icon: 'warning',
         allowOutsideClick: false,
         onOpen: () => {
                  swal.hideLoading()
         }
      });
      
      $('#retur_card').loading('toggle');
   }).done(function() {
      $('#retur_card').loading('toggle');
   }); 

}

modalKoli=()=>{
   $('#modalKoli').modal({
      backdrop: 'static',
      keyboard: false
    });
}

dataRetur =()=>{
   let select = "",
       no = 1,
       toko = btoa(JSON.stringify(dataNRB[selectedTable[0]]));
      param = "toko="+toko;
      fieldKoli = '';
   $('.modal').loading('toggle');
   $.getJSON(link + "/api/retur/data/list?"+param, function(data) {
     
       
      // if(data.data){
         $.each(data.data,function(key,value) {
            fieldKoli+=`
                     <tr>
                           <td>${no++}</td>
                           <td>${value.plu?value.plu:'-'}</td>
                           <td>${value.keterangan?value.keterangan:'-'}</td>
                           <td>${value.retur?value.retur:'-'}</td>
                           <td>${value.fisik?value.fisik:'-'}</td>
                           <td>${value.baik?value.baik:'-'}</td>
                           <td>${value.layakretur?value.layakretur:'-'}</td>
                           <td>${value.ba?value.ba:'-'}</td>
                           <td>${value.price?value.price:'-'}</td>
                           <td>${value.ppn?value.ppn:'-'}</td>
                           <td>${value.status?value.status:'-'}</td>
                           <td>${value.tag_idm?value.tag_idm:'-'}</td>
                           <td class="editable">${value.avgcost?value.avgcost:'-'}</td>
                           <td class="editable">${value.retmajalah?value.retmajalah:'-'}</td>
                           <td>${value.lokasi?value.lokasi:'-'}</td>
                           <td>${value.exp_dt?value.exp_dt:'-'}</td>
                           <td>${value.flag_pindah?value.flag_pindah:'-'}</td>
                     </tr>
                  `;
                  dataRetur[value.plu] = value;
         });

         console.log(data.type.includes("NonF"))
        if (data.type.includes("NonF")) {
         
           $('.NonF').show()
           $('.F').hide()
        } else {
            $('.NonF').hide()
            $('.F').show()
        }
         
         $("#table-content-retur").append(fieldKoli);

      // }

   }).fail(function(data) {
      Swal.fire({
         title: data.responseJSON.messages,
         html: '',
         icon: 'warning',
         allowOutsideClick: false,
         onOpen: () => {
                  swal.hideLoading()
         }
      });
      
      $('.modal').loading('toggle');
   }).done(function() {
      $('.modal').loading('toggle');
   }); 

}

get_data_koli =()=>{
   let select = "",
       toko = btoa(JSON.stringify(dataNRB[selectedTable[0]]));
      param = "toko="+toko;
      fieldKoli = '';
   $('.modal').loading('toggle');
   $.getJSON(link + "/api/retur/data/koli?"+param, function(data) {
     
       
      // if(data.data){
         $.each(data.data,function(key,value) {
            fieldKoli+=`
                     <tr>
                           <td>${value.nokoli?value.nokoli:'-'}</td>
                           <td>${value.plu?value.plu:'-'}</td>
                           <td>${value.qty_dspb?value.qty_dspb:'-'}</td>
                     </tr>
                  `;
                  dataNRB[value.kodetoko] = value;
         });

         
         $("#table-content-koli").append(field);

      // }

   }).fail(function(data) {
      Swal.fire({
         title: data.responseJSON.messages,
         html: '',
         icon: 'warning',
         allowOutsideClick: false,
         onOpen: () => {
                  swal.hideLoading()
         }
      });
      
      $('.modal').loading('toggle');
   }).done(function() {
      modalKoli();
      $('.modal').loading('toggle');
   }); 

}

selected_modal_table=(value)=>{
   let selected_so = null;
   if ( value == undefined) {
       Swal.fire({
          title: 'Data SO blm di pilih',
          html: '',
          icon: 'warning',
          allowOutsideClick: false,
          onOpen: () => {
                  swal.hideLoading()
          }
 
       });
       kodeso = null;
       tglso = null;
       tahap = null;
       return false;
   }
   selected_so = dataSO[value[0]],
   condition = selected_so.mso_flagcetak === selected_so.mso_flagtahap? true:false;
   kodeso = selected_so.mso_kodeso;
   tglso = selected_so.mso_tglso;
   tahap = selected_so.mso_flagtahap;
   txtTahap = 'Tahap '+parseInt(value[2]);
   
   $('#modalSO').modal('hide')
}

format_currency=(data)=>{
   var value = data.toLocaleString();
   n = parseInt(value.replace(/\D/g, ''), 10);
   return n.toLocaleString();

}

cetak_report=(condition)=>{
   let url = link+'/',
       title = '';

   if( selectedTable.length == 0){
      Swal.fire({
         title: 'Gagal',
         html: 'Data NRB belum Dipilih',
         icon: 'warning',
         allowOutsideClick: false,
         onOpen: () => {
                  swal.hideLoading()
         }
      });
      return false;
   }
       switch(condition) {
         case 'cek_kks':
            url += 'api/retur/print/cek_kks';
            title = "Cek KKS";
   
           break;
         case 'cek_kksa':
            url += 'api/retur/print/cek_kksa';
           
           break;
         default:
           break;
       }
   Swal.fire({
      title: "Anda Yakin Cetak "+title+" ?",
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: `Ya`,
      denyButtonText: `Tidak`,
  }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.value) {

         $('#retur_card').loading('toggle');
         let select = '';
         $.getJSON(url, function(data) {
         }).fail(function(data) {
            $('#retur_card').loading('toggle');
            Swal.fire({
               title: 'Gagal',
               html: 'Harap Periksa kembali File Anda',
               icon: 'warning',
               allowOutsideClick: false,
               onOpen: () => {
                        swal.hideLoading()
               }
            });
            console.log(data)
         }).done(function(data) {
            $('#retur_card').loading('toggle');
            console.log(data)
         })
       
      }
      /* Read more about isConfirmed, isDenied below */
  });
}


