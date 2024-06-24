let selectedTable,
    selectedData  =  [],
    selectedToko =[],
    dataToko =[],
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
      // $("#datepicker").datepicker({
      //    format: "dd-MM-yyyy",
      //    autoclose: true,
      //    todayHighlight: true
      // });
      
      $('#form_wt').submit(function(e){
         e.preventDefault();
           formData = new FormData(this)
      })
});

submit_wt =()=>{

   $('#proses-wt').loading('toggle');
   $('#form_wt').submit()
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
                        <tr>
   
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
   
            
            $("#table-content-proseswt").append(field);
            $('.dpp_idm').val(format_currency(data.data.dpp_idm));
            $('.ppn_idm').val(format_currency(data.data.ppn_idm));
            $('.total_idm').val(format_currency(data.data.total_idm));
            $('.dpp_igr').val(format_currency(data.data.dpp_igr));
            $('.ppn_igr').val(format_currency(data.data.ppn_igr));
            $('.total_igr').val(format_currency(data.data.total_igr));
            $('.retur_fisik').val(format_currency(data.data.retur_fisik));
            $('.retur_peforma').val(format_currency(data.data.retur_performa));
         //    $(".rupiah").keyup(function(){
         //       if ($(".rupiah").val().trim().length === 0) {
         //               $(".rupiah").val(0);
         //       }
         //       if ($('input[name="hargaMRC"]').val().trim().length === 0) {
         //               $('input[name="hargaMRC"]').val(0);
         //       }
         //       var n = parseInt($(this).val().replace(/\D/g, ''), 10);
         //       $(this).val(n.toLocaleString());
         //       //do something else as per updated question
         //   });
         var selisih = data.data.total_idm - data.data.total_igr;
         $('.total_selisih').html(format_currency(selisih));
         
           
         }
         $('#proses-wt').loading('toggle');
      }); 

}

format_currency=(data)=>{
   var value = data.toLocaleString();
   n = parseInt(value.replace(/\D/g, ''), 10);
   return n.toLocaleString();

}

