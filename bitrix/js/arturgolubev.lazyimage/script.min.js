(function() {
	'use strict';
	
	if (!!window.JAgLazyimage)
		return;

	window.JAgLazyimage = function(params) {
		this.params = params;

        var self = this;
        if(window.frameCacheVars !== undefined){
            BX.addCustomEvent("onFrameDataReceived", function(json){
                if(self.params.debug){
                    console.log("Lazyimage Init = composite");
                }

                self.initImages();
                self.checker();
            });
        }else{
            document.addEventListener("DOMContentLoaded", function(event) {
                if(self.params.debug){
                    console.log("Lazyimage Init = ready");
                }
                
                self.initImages();
                self.checker();
            });
        }
	};

    
	window.JAgLazyimage.prototype.initImages = function(){
        var self = this;

        var lazyElements = document.querySelectorAll("." + self.params.class_lazy + ":not(." + self.params.class_lazy_inited + ")");

        if(lazyElements.length){
            if(self.params.debug){
                console.log('Lazy Init Length = ' + lazyElements.length);
            }

            var agLazyLoader = lozad(lazyElements);
            agLazyLoader.observe();

            lazyElements.forEach(function(current) {
                current.classList.add(self.params.class_lazy_inited)
            });
        }
    };
    
	window.JAgLazyimage.prototype.checker = function(){
        var self = this;

        setTimeout(function(){
            if(self.params.debug){
                console.log("Lazy: checks");
            }

            self.initImages();
            self.checker();
        }, 1500);
    };
})();