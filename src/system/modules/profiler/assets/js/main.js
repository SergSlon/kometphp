// Place your application javascript here
(function(window, $) {
    $(function(){
        if(window.parent.location.href == window.location.href){
            $("*[data-event='profiler.resize']").hide();
        }else{
            var $iframe = $(window.parent.document.getElementById('kometphp_profiler'));
            if($iframe){
                $('html',window.parent.document).addClass("with-kometphp-profiler");
                $("*[data-event='profiler.resize']").click(function(e){
                    var $this = $(this);
                    if($iframe.hasClass('maximized')){
                        $iframe.animate({"height":40}).removeClass('maximized');
                        $this.removeClass('maximized');
                        $('i',$this).removeClass('icon-resize-small').addClass('icon-resize-full');
                    }else{
                        $iframe.animate({"height": ($(window.parent).height() / 2) + 40}).addClass('maximized');
                        $this.addClass('maximized');
                        $('i',$this).removeClass('icon-resize-full').addClass('icon-resize-small');

                        if($(".profiler-container .tab-pane.active").length == 0){
                            $('.profiler-bar a[data-toggle="tab"]:first').trigger("click");
                        }
                    }
                });
                $(".profiler-bar a[data-toggle='tab']").click(function(e){
                    if(!$iframe.hasClass('maximized')){
                        $("*[data-event='profiler.resize']:first").trigger("click");
                    }
                });
                $('body',window.parent.document).click(function(e){
                    if($iframe.hasClass('maximized')){
                        var $triggers = $("*[data-event='profiler.resize']");
                        $iframe.animate({"height":40}).removeClass('maximized');
                        $triggers.removeClass('maximized');
                        $('i',$triggers).removeClass('icon-resize-small').addClass('icon-resize-full');
                    }
                });
            }
        }
    });
}(window, jQuery));