angular
.module('ngNova')
.factory('myEmailShareService', function($rootScope) {
 
 	var emailShareService = {};

	emailShareService.emailObject = {
	  	urlgetdata: 'email/getData',
	    emailto: 'Rocio <rociom@novatechnology.com.ec>',
	    emailcc: '<alexmero@novatechnology.com.ec>',
	    emailsubject: 'envio',
	    emailcontent: 'contenido de email',
	    showPreviewEmail: false,
	    firstTimeEmail: false,
	    attachments: [],
	    attachlist: [],
	    internalattachments: [],
	    internalattachlist: [],
	};

    emailShareService.prepForBroadcast = function(emailObject) {
        this.emailObject.urlgetdata = emailObject.urlgetdata,
	    this.emailObject.emailto = emailObject.emailto,
	    this.emailObject.emailcc = emailObject.emailcc,
	    this.emailObject.emailsubject = emailObject.emailsubject,
	    this.emailObject.emailcontent = emailObject.emailcontent,
	    this.emailObject.attachments = emailObject.attachments,
	    this.emailObject.attachlist = emailObject.attachlist,
	    this.emailObject.internalattachments = emailObject.internalattachments,
	    this.emailObject.internalattachlist = emailObject.internalattachlist,
	    this.emailObject.showPreviewEmail = emailObject.showPreviewEmail,
	    this.emailObject.firstTimeEmail = emailObject.firstTimeEmail,
	    this.broadcastItem();
    };

    emailShareService.broadcastItem = function() {
        $rootScope.$broadcast('handleEmailBroadcast');
    };

    emailShareService.broadcastCloseItem = function() {
        $rootScope.$broadcast('handleCloseEmailBroadcast');
    };

    return emailShareService;
});