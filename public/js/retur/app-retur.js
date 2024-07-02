let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    dataNRB =[],
    dataKoli =[],
    listToko =[],
    search  =  false,
    page = 1,
    field = null,
    fieldKoli = null,
    formData = null,
    cabang = null;

$(document).ready(function(){
      /**
       * table_nrb
       */
         $('#table_NRB tbody').on('click', 'tr', function () {
            
            $('#table_NRB tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');

            selectedTable = $(this).find('td').map(function (data) {
                  return $(this).text();
            }).get();
            get_data_koli();

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
                           <td>${value.istype?value.istype:'-'}</td>
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
get_data_koli =()=>{
   console.log(dataNRB[selectedTable[0]])
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

