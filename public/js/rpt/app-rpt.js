let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    dataToko =[],
    listToko =[],
    search  =  false,
    page = 1,
    field = null,
    formData = null,
    stauts_toko = null;

$(document).ready(function(){
   
      $('.select2').select2({
         allowClear: false
      }); 
      
    /**
     * add html in select 2
     */

        function formatOption(option) {
                if ($(option.element).data('no') !== undefined) {
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

     /**
      * end add html in select 2
      */


    /**
     * submit modal and show pdf
     */

        $('#modal-form').submit(function (e) {
                e.preventDefault();
                let form = $(this),
                    text = form.find('.text').val()?$('.text').val():'Apakah Anda ingin Menyimpan Data Ini?',
                    url = form.attr('action'),
                    formData = new FormData(this),
                    method = form.attr('method') == undefined ? 'PUT' : 'POST';
        
            
                Swal.fire({
                    title: text,
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: `Ya`,
                    denyButtonText: `Tidak`,
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.value) {

                        $('.modal').loading('toggle');
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
                                console.log(response.url,response)
                                $('#pdfFrame').attr("src",response.url)
                                $('.modal').loading('toggle');
                                $('#pdfFrame').show();
                            
                            },
                            error: function (xhr) {
            
                                
                                $('.modal').loading('toggle');
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
    /**
     * End submit modal and show pdf
     */

     $('#pdfFrame').hide();
     $('.toko_2').hide();
     $('.nopb').show();
});

modal_toko_pb=(jenis = null,text=null,url=null,toko2 = false,omi=false)=>{
    if(toko2){
        $('.toko_2').show();
        $('.nopb').hide();
    }else{
        $('.toko_2').hide();
        $('.nopb').show();

    }
    if (omi) {
        if (stauts_toko !== 'omi') {
            get_toko(true);
            stauts_toko = 'omi';
        }
    }else{
        if (stauts_toko !== 'omi') {
            get_toko(false);
            stauts_toko = 'idm';
        }
    }
   $('.modal-title').html('');
   $('#modal-pb-toko').modal();
   $('.modal-title').html(jenis);
   $('.text').val(text+' ?')
   $('.form_data').attr('action',link+url);

}

get_toko=(omi = false)=>{


    $("#toko").html('');
    $("#toko_2").html('');
      $('#retur_card').loading('toggle');
      let select = '',
          param = omi?'?type=omi':'?type=idm';
        $.getJSON(link + "/api/report/data/toko"+param, function(data) {
            select+='<option selected disabled> Pilih TOKO</option>';
        
            if(data.length){
                $.each(data,function(key,value){
                    select+=` <option value="${value.tko_kodeomi}" >${value.tko_kodeomi}-${value.tko_namaomi}</option>`;
                    listToko[value.tko_kodeomi] = value;

                });
                $("#toko").append(select);
                $("#toko_2").append(select);
            }else{
                select+='<option value="" disabled data-nopb="" data-tgdsp="Data Tidak Ada" data-no=""> Data Tidak ada/option>';
             }
        }).fail(function() {
            $('#retur_card').loading('toggle');
        }).done(function() {
            $('#retur_card').loading('toggle');
        })
}
get_pb=(toko = null)=>{

      
      $("#nopb").html('');
      $("#nopb").val('');
      $('.modal').loading('toggle');
      $('#nopb').prop('disabled',false);
      let select = '',
          no = 1;
      $.getJSON(link + "/api/report/pb/omi?toko="+toko, function(data) {
         select+='<option selected disabled> Pilih PB</option>';
         if(data.length){
            $.each(data,function(key,value){
                  select+=` <option value="${value.nopb} / ${value.tgdsp}" data-nopb="${value.nopb}" data-tgdsp="${value.tgdsp}" data-no="${no++}"> </option>`;
                  listToko[value.nopb] = value;

            });
            $("#nopb").append(select);
         }else{
            select+='<option value="" disabled data-nopb="" data-tgdsp="Data Tidak Ada" data-no=""> Data Tidak ada/option>';
         }
      }).fail(function() {
         $('.modal').loading('toggle');
      }).done(function() {
         $('.modal').loading('toggle');
      })
}

submit_modal =()=>{

   $('#modal-form').submit();
  
}

