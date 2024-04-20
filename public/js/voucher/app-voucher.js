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
      getVoucherData();
      // getDataRak();
      // $("#by-plu").prop("checked", true);
      // toggleInput('by-plu')

      $(".voucher-label").hide();
});



getVoucherData =()=>{
   let select = "";
       listDataPLU = [];

   // $('#label-tag').loading('toggle');
   $.getJSON(link + "/api/voucher/data", function(data) {
     
      if(data){
         $.each(data,function(key,value){
               select+=` <option value="${value.zon_kode}" >${value.zon_kode}</option>`;
               listDataPLU[value.prdcd] = value;

         });
         $("#zona").append(select);
      }

   })

   // $('#label-tag').loading('toggle');

}

