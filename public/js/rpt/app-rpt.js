let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    dataToko =[],
    listToko =[],
    search  =  false,
    page = 1,
    field = null,
    formData = null,
    cabang = null;

$(document).ready(function(){
      /**
       * table_plu
       */
      // $('#table_plu input[type="checkbox"]').click(function () {
      //       // Toggle the 'selected' class on the parent row
      //       $(this).closest('tr').toggleClass('selected-row', this.checked);
      // });
      // $('#table_plu tbody').on('click', 'tr', function () {

      //    $(this).toggleClass('selected-row');
      //    selectedTablePLU = $(this).find('td').map(function (data) {
      //          return $(this).text();
      //    }).get();
      //    $(this).find('input[type="checkbox"]').prop('checked', function (i, oldProp) {
      //       if ($(this).is(':checked')) {
      //          addPlu(selectedTablePLU[1],false)
      //    } else {
      //          addPlu(selectedTablePLU[1],true)
      //    }
      //       return !oldProp;
      //    });
         

      // });

      $('.select2').select2({
         allowClear: false
      }); 
      get_toko();
      // $("#datepicker").datepicker({
      //    format: "dd-MM-yyyy",
      //    autoclose: true,
      //    todayHighlight: true
      // });
      

      function formatOption(option) {
         if (!option.id) {
             return option.text;
         }

         var $option = $(
            '<div class="select2-result-option">' +
            `
              <table style="padding: 8px;font-family: arial, sans-serif;border-collapse: collapse;border:1px solid black;text-align:center;">
               <tr>
                  <th width="80" style="font-size:7px;">No.</th>
                  <th width="100" style="font-size:7px;">No PB</th>
                  <th width="200" style="font-size:7px;">Tgl DSP</th>
               </tr>
                <tr>
                  <th width="80">`+$(option.element).data('no')+`</th>
                  <th width="100">`+$(option.element).data('nopb')+`</th>
                  <th width="200"> `+$(option.element).data('tgdsp')+`</th>
                </tr>
              </table>
            `
            +'</div>'
         );
         return $option;
     }

     function formatSelection(option) {
         if($(option.element).data('nopb') !== undefined && $(option.element).data('tgdsp')){
            return $(option.element).data('nopb')+' / '+$(option.element).data('tgdsp');
         }else{
            return $(option.element).html()
         }
     }

     $('#nopb').select2({
         placeholder: 'Select an item',
         allowClear: true,
         templateResult: formatOption,
         templateSelection: formatSelection
     });
});

modal_toko_pb=(jenis = null,text=null,url=null)=>{
   $('.modal-title').html('');
   $('#modal-pb-toko').modal();
   $('.modal-title').html(jenis);
   $('.text').val(text+' ?')
   $('.form_data').attr('action',link+url);

}

get_toko=()=>{

      $('#retur_card').loading('toggle');
      let select = '';
      $.getJSON(link + "/api/retur/data/toko", function(data) {
      
         if(data){
            $.each(data,function(key,value){
                  select+=` <option value="${value.tko_kodeomi}" >${value.tko_kodeomi}-${value.tko_kodecustomer}</option>`;
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
get_pb=(toko = null)=>{

      $('.modal').loading('toggle');
      $('#nopb').prop('disabled',false);
      let select = '',
          no = 1;
      $.getJSON(link + "/api/report/pb/omi?toko="+toko, function(data) {
      
         if(data){
            $.each(data,function(key,value){
                  select+=` <option value="${value.nopb} / ${value.tgdsp}" data-nopb="${value.nopb}" data-tgdsp="${value.tgdsp}" data-no="${no++}"> </option>`;
                  listToko[value.nopb] = value;

            });
            $("#nopb").append(select);
         }
      }).fail(function() {
         $('.modal').loading('toggle');
      }).done(function() {
         $('.modal').loading('toggle');
      })
}

submit_modal =()=>{
   $('#modal-form').submit(function (e) {
      e.preventDefault();
      let form = $(this),
          text = form.find('.text').val()?$('.text').val():'Apakah Anda ingin Menyimpan Data Ini?',
          url = form.attr('action'),
          formData = new FormData(this),
          method = form.attr('method') == undefined ? 'PUT' : 'POST',
          /**
           * defind input when use multi form
           */
          multipleForm = (formData.getAll('multipleForm')[0])? formData.getAll('multipleForm')[0]:'',
          /**
           * defind link redirect , download or run next function after submit
           * 
           * it get value in input (for redirect and download) and get function in onchange (for run next function)
           */
          runNext =  (document.getElementById('runNext'+multipleForm))?document.getElementById('runNext'+multipleForm).onchange:null,
          redirect = formData.getAll('redirect'+multipleForm)[0],
          download = formData.getAll('download'+multipleForm)[0];
          
      form.find('.form-control').removeClass('is-invalid');
      form.find('.help-block').remove();
      /**
       * delete in Form Data
       */
      formData.delete('multipleForm')
      formData.delete('redirect'+multipleForm)
      formData.delete('download'+multipleForm)
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
               Swal.fire({
                  title: 'Loading..',
                  html: '',
                  allowOutsideClick: false,
                  onOpen: () => {
                          swal.showLoading()
                  }
              });
              $.ajax({
                  url: url,
                  method: method,
                  data: new FormData(this),
      
                  success: function (response) {
                      /**
                       * response property
                       * 
                       * messages => for the custom message 
                       * download => for redirect link download
                       * redirect => for redirect to another page
                       * callback => for execute respose data callback
                       */
                      let messages = response.messages?response.messages:'Data Berhasil Diproses';
                      Swal.fire(
                          'Berhasil',
                           messages,
                          'success'
                      )
                     if((download !== '' && download !== null && download !== undefined ) || (response.download !== '' && response.download !== null && response.download !== undefined)){
                          download = response.download?response.download:download;
                          window.open(download,'_blank');
                     }
                     if((redirect !== '' && redirect !== null && redirect !== undefined) || (response.redirect !== '' && response.redirect !== null && response.redirect !== undefined)){
                         redirect = response.redirect?response.redirect:redirect;
                         window.location.href = redirect
                     } 
                     console.log(response,response.callback !== '' && response.callback !== null && response.callback !== undefined);
                     if(runNext !== '' && runNext !== null && runNext !== undefined){
                          
                          if (response.callback !== '' && response.callback !== null && response.callback !== undefined) {
                              $('#runNext'+multipleForm).val(JSON.stringify(response.callback))
                              
                              next_callback_function = (document.getElementById('runNext'+multipleForm));
                              next_callback_function.dispatchEvent(new Event('change')).parameter = response.callback;
                          } else {
                              runNext.call()
                          }
                     } 
                  },
                  error: function (xhr) {
  
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
  });
}

