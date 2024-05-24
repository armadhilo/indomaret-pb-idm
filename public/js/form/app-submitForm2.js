$('.form_data').submit(function (e) {
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