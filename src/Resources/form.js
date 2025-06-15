(function ($) {
    $.fn.ajaxform = function (options) {
        const defaults = {
            onSuccess: null,
            onError: null
        },
            settings = $.extend({}, defaults, options),
            debug = false;

        return this.each(function () {
            const form = $(this);
            form.attr('data-ajaxform', true);
            let clickedSubmit = null;

            form.find(':submit').on('click', function () {
                clickedSubmit = this;
            });

            form.on('submit', function (event) {
                event.preventDefault();

                const submitButton = $(this).find(':submit');

                if ($(clickedSubmit).attr('name') && $(clickedSubmit).val()) {
                    $('<input type="hidden">')
                        .attr('name', $(clickedSubmit).attr('name'))
                        .val($(clickedSubmit).val())
                        .appendTo($(this));
                }

                submitButton.prop('disabled', true);

                form.trigger('ajaxform.submit', [form]);

                let formData = form.serializeArray();
                const activeElement = document.activeElement;

                if (activeElement && form.has(activeElement).length && $(activeElement).is('[type="submit"]')) {
                    if ($(activeElement).attr('name') && $(activeElement).val()) {
                        formData.push({
                            name: $(activeElement).attr('name'),
                            value: $(activeElement).val()
                        })
                    }
                }

                let urlObj = new URL(window.location.href);

                if (form.closest('[data-url]').length > 0) {
                    urlObj = new URL(window.location.origin + form.closest('[data-url]').attr('data-url'));
                }

                urlObj.searchParams.append('ajax', 'form');
                urlObj.searchParams.append('form', form.attr('id'));

                $.ajax({
                    url: urlObj.toString(),
                    type: form.attr('method') || 'POST',
                    data: formData,
                    success: function (response, status, xhr) {
                        if (debug) {
                            console.log('ajaxform success', response, status, xhr);
                        }

                        let preventDefault = false;

                        form.trigger('ajaxform.success', [response, form, function () {
                            preventDefault = true;
                        }]);

                        if (!preventDefault) {
                            if (typeof response === 'object' && response.type === 'redirect' && response.url) {
                                window.location.href = response.url;
                            } else {
                                let currentElement = document.getElementById(form.attr('id'));

                                if (debug) {
                                    console.log('ajaxform innerHTML', currentElement);
                                }

                                if (currentElement) {
                                    currentElement.innerHTML = response;
                                }

                                $('.confirm-close').on('click', function (e) {
                                    e.preventDefault();
                                    formData.push({ name: 'correction', value: true });
                                    $modal = $(this).closest('.modal');

                                    $.ajax({
                                        url: urlObj.pathname,
                                        type: form.attr('method') || 'POST',
                                        data: formData,
                                        success: function (response, status, xhr) {
                                            submitButton.prop('disabled', false);
                                            $newBody = $modal.find('.modal-body');
                                            $modal.find('form').remove();
                                            $newBody.html(response);
                                        }
                                    });
                                });

                                $('.confirm-accept').on('click', function (e) {
                                    e.preventDefault();
                                    formData.push({ name: 'confirm', value: true });
                                    const $triggerAccept = $(this);

                                    $.ajax({
                                        url: urlObj.toString(),
                                        type: form.attr('method') || 'POST',
                                        data: formData,
                                        success: function (response, status, xhr) {
                                            if (typeof response === 'object' && response.type === 'redirect' && response.url) {
                                                $modal = $triggerAccept.closest('.modal');

                                                let responseUrl = response.url.split('/');
                                                let urlArr = responseUrl.filter(function (e) {
                                                    return e;
                                                });

                                                if (urlArr.length > 2) {
                                                    $.ajax({
                                                        url: response.url,
                                                        type: form.attr('method') || 'POST',
                                                        success: function (response, status, xhr) {
                                                            submitButton.prop('disabled', false);
                                                            form.trigger('ajaxform.nextStep', [form]);

                                                            $newBody = $modal.find('.modal-body');
                                                            $modal.find('form').remove();
                                                            $newBody.html(response);
                                                        }
                                                    });

                                                } else {
                                                    $modal.find('.modal-header').find('[data-bs-dismiss="modal"]').trigger('click');
                                                    $modal.remove();
                                                    window.location.href = response.url;
                                                }
                                            } else {
                                                submitButton.prop('disabled', false);
                                            }
                                        }
                                    });
                                });
                            }
                        }

                        if (typeof settings.onSuccess === 'function') {
                            settings.onSuccess(response, form);
                        }
                    },
                    error: function (xhr, status, error) {
                        if (debug) {
                            console.log('ajaxform error', xhr, status, error);
                        }

                        let preventDefault = false;

                        form.trigger('ajaxform.error', [xhr, status, error, form, function () {
                            preventDefault = true;
                        }]);

                        if (!preventDefault) {
                            if (typeof xhr.responseJSON === 'object' && xhr.responseJSON.type === 'redirect' && xhr.responseJSON.url) {
                                window.location.href = xhr.responseJSON.url;
                            }

                            if (typeof settings.onError === 'function') {
                                settings.onError(xhr, status, error, form);
                            }
                        }

                        console.error('Error:', error);

                        submitButton.prop('disabled', false);
                    }
                });
            });
        });
    };
})(jQuery);