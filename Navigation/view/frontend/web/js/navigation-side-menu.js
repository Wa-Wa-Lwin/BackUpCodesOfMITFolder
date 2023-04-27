define([
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'jquery/ui'
], function($) {
    'use strict';
    return function() {
        var menuList = JSON.stringify($menuList);
        var data = JSON.parse(menuList);
        // $('#custom-side-menu').html(generateCode(data));

        $(document).ready(function() {
            $('body').on('click', '#custom-side-menu li>a', function(event) {
                event.preventDefault();
                var url = $(this).parent().attr('url');
                var id = $(this).parent().attr('id');

                if (url && id) {
                    if (id.split('-').includes('b')) {
                        checkForwardBackward(url, id, data);
                    } else {
                        $('#custom_menu__toggle').prop('checked', false);
                        $(location).attr('href', url);
                    }
                }
            });

            $('body').on('click', '#custom-side-menu li [class*="-carret"]', function(event) {
                event.preventDefault();
                var url = $(this).parent().attr('url');
                var id = $(this).parent().attr('id');
                var customClass = $(this).parent().attr('class');

                if (customClass === 'side-no-child' && url) {
                    $('#custom_menu__toggle').prop('checked', false);
                    $(location).attr('href', url);
                    return;
                }

                checkForwardBackward(url, id, data);
            });

        });

        function checkForwardBackward(url, id, data) {
            if (url && id) {
                var isBackWard = false;
                var idArr = id.split('-');
                if (idArr.includes('b')) {
                    isBackWard = true;
                    idArr.shift();
                }
                var result = idArr.map(Number);
                if (result.length === 1) {
                    if (isBackWard) {
                        $('#custom-side-menu').html(generateCode(data, '', '', '', true));
                    } else if (url === data[result[0] - 1].url) {
                        var body = '<li id="b-' + result[0] + '" class="back-menu" url="' + data[result[0] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' + data[result[0] - 1].name + '</a></li>';
                        $('#custom-side-menu').html(generateCode(data[result[0] - 1].children, body, result[0], url));
                    }
                } else if (result.length === 2) {
                    if (url === data[result[0] - 1].children[result[1] - 1].url) {
                        var body = '<li id="b-' + result[0] + '" class="back-menu" url="' + data[result[0] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' + data[result[0] - 1].name + '</a></li>';

                        if (isBackWard) {
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children, body, result[0]));
                        } else {
                            body += '<li id="b-' + result.join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                                data[result[0] - 1].children[result[1] - 1].name + '</a></li>';
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children, body, result[0] + '-' + result[1], url));
                        }
                    }
                } else if (result.length === 3) {
                    if (url === data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].url) {
                        var body = '<li id="b-' + result[0] + '" class="back-menu" url="' + data[result[0] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' + data[result[0] - 1].name + '</a></li>';
                        body += '<li id="b-' + [result[0], result[1]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].name + '</a></li>';

                        if (isBackWard) {
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children, body, result[0] + '-' +
                                result[1]));
                        } else {
                            body += '<li id="b-' + [result[0], result[1], result[2]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                                data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].name + '</a></li>';
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children, body, result[0] + '-' +
                                result[1] + '-' + result[2], url));
                        }
                    }
                } else if (result.length === 4) {
                    if (url === data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].url) {
                        var body = '<li id="b-' + result[0] + '" class="back-menu" url="' + data[result[0] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' + data[result[0] - 1].name + '</a></li>';
                        body += '<li id="b-' + [result[0], result[1]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].name + '</a></li>';
                        body += '<li id="b-' + [result[0], result[1], result[2]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].name + '</a></li>';

                        if (isBackWard) {
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children, body, result[0] + '-' +
                                result[1] + '-' + result[2]));
                        } else {
                            body += '<li id="b-' + result.join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                                data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].name + '</a></li>';
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].children, body, result[0] + '-' +
                                result[1] + '-' + result[2] + '-' + result[3], url));
                        }
                    }
                } else if (result.length === 5) {
                    if (url === data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].children[result[4] - 1].url) {
                        var body = '<li id="b-' + result[0] + '" class="back-menu" url="' + data[result[0] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' + data[result[0] - 1].name + '</a></li>';
                        body += '<li id="b-' + [result[0], result[1]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].name + '</a></li>';
                        body += '<li id="b-' + [result[0], result[1], result[2]].join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].name + '</a></li>';

                        body += '<li id="b-' + result.join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                            data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].name + '</a></li>';

                        if (isBackWard) {
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].children, body, result[0] + '-' +
                                result[1] + '-' + result[2] + '-' + result[3]));
                        } else {
                            body += '<li id="b-' + result.join('-') + '" class="back-menu" url="' + data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].children[result[4] - 1].url + '"><span class="backward-carret"></span><a href="#">Back to ' +
                                data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].name + '</a></li>';
                            $('#custom-side-menu').html(generateCode(data[result[0] - 1].children[result[1] - 1].children[result[2] - 1].children[result[3] - 1].children[result[4] - 1].children, body, result[0] + '-' +
                                result[1] + '-' + result[2] + '-' + result[3] + '-' + result[4], url));
                        }
                    }
                }
            }
        }

        function generateCode(data, listPrefix = '', idPrefix = '', route = '', parent = false) {
            var body = '';
            if (data && data.length > 0) {
                body += listPrefix;
                if (listPrefix) {
                    body += '<div class="spacer"></div>';
                }
                // body += '<ul id="testing">';
                $.each(data, function(index, value) {
                    var child = value.children.length > 0 ? 'side-child' : 'side-no-child';
                    var spanCarret = value.children.length > 0 ? '<span class="forward-carret"></span>' : '';
                    if (parent) {
                        if (value.productCount > 0 || value.children.length > 0) {
                            if (idPrefix) {
                                body += '<li id="' + (idPrefix) + '-' + (index + 1) + '" class="' + child + '" url="' + value.url + '"><a href="#">' + value.name + '</a>' + spanCarret + '</li>';
                            } else {
                                body += '<li id="' + (index + 1) + '" class="' + child + '" url="' + value.url + '"><a href="#">' + value.name + '</a>' + spanCarret + '</li>';
                            }
                        }
                    } else {
                        if (idPrefix) {
                            body += '<li id="' + (idPrefix) + '-' + (index + 1) + '" class="' + child + '" url="' + value.url + '"><a href="#">' + value.name + '</a>' + spanCarret + '</li>';
                        } else {
                            body += '<li id="' + (index + 1) + '" class="' + child + '" url="' + value.url + '"><a href="#">' + value.name + '</a>' + spanCarret + '</li>';
                        }
                    }

                });
                // body += '</ul>';
            } else {
                $('#custom_menu__toogle').prop('checked', false);
                //$(location).attr('href', route);
            }
            return body;
        }
    };
});