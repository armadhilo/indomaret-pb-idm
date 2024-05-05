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
      $('#scrollContainer').on('scroll', function () {
         var container = $(this);
         if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight) {
         // Load more data when scrolled to the bottom
         // view();
         }
      });
      
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
      getDataZona();
      // getDataRak();
      // $("#by-plu").prop("checked", true);
      // toggleInput('by-plu')

      $(".monitoring-label").hide();
});

download_txt=()=>{
   $('#downloadBtn').click(function(){
      var textData = '';
      textData += "ISI KOLI\n";
      textData += "TOKO  : T7G5 - MODERN BOULEVARD\n";
      textData += "NO PB : 901064 / 23-03-2024\n\n";
      textData += "No PICK : 63437\n";
      textData += "No KOLI : 020063437001\n";
      textData += "========================================\n";
      textData += " NO. NAMA BARANG            PLU      QTY\n";
      textData += "========================================\n";
      textData += "   1 POP MIE AYAM 75G      0037141    24\n";
      textData += "   2 SOSRO TEH BTL TPK 1L  1141511    12\n";
      textData += "   3 SARIMI GOR.AYM KK125  1179331    24\n";
      textData += "   4 QTELA KRPK BLDO 180G  1254071    12\n";
      textData += "========================================\n";
      textData += "TOTAL : 4 ITEM / RP + PPN  :   433.536\n";
      
      // Create a blob with the text data
      var blob = new Blob([textData], { type: 'text/plain;charset=utf-8' });
      
      // Create a temporary anchor element
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(blob);
      
      // Set the href attribute of the anchor element to the Blob URL
      a.href = url;
      
      // Set the download attribute to specify the filename
      a.download = 'text_file.txt';
      
      // Programmatically click the anchor element to trigger the download
      a.click();
      
      // Release the Object URL resource
      window.URL.revokeObjectURL(url);
    });
}

toggleInput =(nameClass,deleteVar)=>{

   $('#label-tag').loading('toggle');
   let className = '.'+nameClass;
   
   $('.input-form').hide();
   $('.input-data').prop('disabled',true);
   $(className).prop('disabled',false);
   $(className).show();

   $('#label-tag').loading('toggle');

}

changePRDCD=(data)=>{

   let prdcd = $("#prdcd").val();

   view(prdcd,null);
   
}


getDataZona =()=>{
   let select = "";
       listDataPLU = [];

   // $('#label-tag').loading('toggle');
   $.getJSON(link + "/api/monitoring/zona", function(data) {
     
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

getDataRak =()=>{
   let select = "";
       listDataRak = [];

   $('#label-tag').loading('toggle');
   $.getJSON(link + "/api/data/rak", function(data) {
     
      if(data){
         $.each(data,function(key,value){
               select+=` <option value="${value.subrak}" >(${value.subrak})</option>`;
               listDataRak[value.prdcd] = value;

         });
         $("#sub-rak").append(select);
      }

   })

   $('#label-tag').loading('toggle');

}

view =(prdcd = null,rak = null)=>{
   // reset_selected();

   let select = "",
       param = "",
       kategori=null;
       dataPLU = [];

       kategori = $(".kategori").val();
       if (prdcd) {
         param = "prdcd="+prdcd;
      } else {
          param = "rak="+rak;
         
       }
       param = param+"&kategori="+kategori;

      $('#label-tag').loading('toggle');
      $.getJSON(link + "/api/check/data?"+param, function(data) {
      // list data member
         $.each(data,function(key,value) {
            field+=`
                     <tr>

                           <td><input type="checkbox" value="1"></td>
                           <td scope="row">${value.temp_plu?value.temp_plu:'-'}</td>
                           <td>${value.temp_recordid?value.temp_recordid:'-'}</td>
                           <td>${value.temp_subrak?value.temp_subrak:'-'}</td>
                           <td>${value.prd_deskripsipanjang?value.prd_deskripsipanjang:'-'}</td>
                           <td>${value.prd_unit?value.prd_unit:'-'}</td>
                           <td>${value.prd_kodetag?value.prd_kodetag:'-'}</td>
                     </tr>
                  `;
                  dataPLU[value.kode_member] = value;
         });
      }).fail(function(jqXHR, textStatus, errorThrown) {
         // Error callback
         Swal.fire({
            title: 'Gagal',
            html: jqXHR.responseJSON.messages,
            icon: 'warning',
            allowOutsideClick: false,
            onOpen: () => {
                    swal.hideLoading()
            }
        });

         $('#label-tag').loading('toggle');
     }).done(function() {
         $(".list-plu").show();
         $('#label-tag').loading('toggle');
         $("#table-content").html(field);
      }); 

}

// pencarian=()=>{
//    reset_selected();
// }

selectedValue =(kode_member)=>{
   selectedData  = dataPLU[kode_member];
}

reset_selected=()=>{
   selectedData  =  [];
   $('.tombol_edit').prop('disabled',true);
   $('#table_member tbody tr').removeClass('selected-row');
   $('.tombol_reset').hide();
   if (search) {
      view();
   }

}

addPlu=(plu,status)=>{
   console.log(listPLU)
   if (status) {
      listPLU.push(plu)
      
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
      listPLU.remove(plu)

      
   }

   console.log(listPLU)
}
