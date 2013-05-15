;(function($) {
function AjaxQueue(override) {
this.override = !!override;
};
AjaxQueue.prototype = {
requests: new Array(),
offer: function(options) {
var _self = this;
var xhrOptions = $.extend({}, options, {
complete: function(jqXHR, textStatus) {
if($.isArray(options.complete)) {
var funcs = options.complete;
for(var i = 0, len = funcs.length; i < len; i++)
funcs[i].call(this, jqXHR, textStatus);
} else {
if(options.complete)
options.complete.call(this, jqXHR, textStatus);
}
_self.poll();
},
beforeSend: function(jqXHR, settings) {
if(options.beforeSend)
var ret = options.beforeSend.call(this, jqXHR, settings);
if(ret === false) {
_self.poll();
return ret;
}
}
});
if(this.override) {
this.replace(xhrOptions);
} else {
this.requests.push(xhrOptions);
if(this.requests.length == 1) {
$.ajax(xhrOptions);
}
}
},
replace: function(xhrOptions) {
var prevRet = this.peek();
if(prevRet != null) {
prevRet.abort();
}
this.requests.shift();
this.requests.push($.ajax(xhrOptions));
},
poll: function() {
if(this.isEmpty()) {
return null;
}
var processedRequest = this.requests.shift();
var nextRequest = this.peek();
if(nextRequest != null) {
$.ajax(nextRequest);
}
return processedRequest;
},
peek: function() {
if(this.isEmpty()) {
return null;
}
var nextRequest = this.requests[0];
return nextRequest;
},
isEmpty: function() {
return this.requests.length == 0;
}
};
var queue = {};
var AjaxManager = {
createQueue: function(name, override) {
return queue[name] = new AjaxQueue(override);
},
destroyQueue: function(name) {
if(queue[name]) {
queue[name] = null;
delete queue[name];
}
},
getQueue: function(name) {
return ( queue[name] ? queue[name] : null);
}
};
$.AM = AjaxManager;
})(jQuery);