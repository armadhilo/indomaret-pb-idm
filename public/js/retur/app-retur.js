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
      get_toko()
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

submit_wt =()=>{

   $('#proses-wt').loading('toggle');

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
         
         $('#proses-wt').loading('toggle');
      })
      .done(function (data) {
         
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

