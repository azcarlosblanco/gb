angular
    .module('ngNova')
    .factory('myShareService', function($rootScope) {

        var shareService = {};

        shareService.diagnosis = {
            showmodal: false,
            id: null,
            name: null,
            displayname: null,
        };

        shareService.concept = {
            showmodal: false,
            id: null,
            name: null,
            display_name: null,
            notify: null,
            deduct_discount: null,
        };

        shareService.specialty = {
            showmodal: false,
            id: null,
            name: null,
            display_name: null,
        };

        shareService.doctor = {
            showmodal: false,
            id: null,
            name: null,
            pid_num: null,
            pid_type: null,
        };

        shareService.hospital = {
            showmodal: false,
            id: null,
            name: null,
            address: null,
        }

        shareService.prepForBroadcastDiagnosis = function(diagnosisObject) {
            this.diagnosis.showmodal = diagnosisObject.showmodal;
            this.diagnosis.id = diagnosisObject.id;
            this.diagnosis.name = diagnosisObject.name;
            this.diagnosis.display_name = diagnosisObject.display_name;
            this.broadcastDiagnosis();
        }

        shareService.prepForBroadcastConcept = function(conceptObject) {
            this.concept.showmodal = conceptObject.showmodal;
            this.concept.id = conceptObject.id;
            this.concept.name = conceptObject.name;
            this.concept.display_name = conceptObject.display_name;
            this.concept.notify = conceptObject.notify;
            this.concept.deduct_discount = conceptObject.deduct_discount;
            this.broadcastConcept();
        }

        shareService.prepForBroadcastSpecialty = function(specialtyObject) {
            this.specialty.showmodal = specialtyObject.showmodal;
            this.specialty.id = specialtyObject.id;
            this.specialty.name = specialtyObject.name;
            this.specialty.display_name = specialtyObject.display_name;
            this.broadcastSpecialty();
        }

        shareService.prepForBroadcastDoctor = function(doctorObject) {
            this.doctor.showmodal = doctorObject.showmodal;
            this.doctor.id = doctorObject.id;
            this.doctor.name = doctorObject.name;
            this.doctor.pid_num = doctorObject.pid_num;
            this.doctor.pid_type = doctorObject.pid_type;
        }

        shareService.prepForBroadcastHospital = function(hospitalObject) {
            this.hospital.showmodal = hospitalObject.showmodal;
            this.hospital.id = hospitalObject.id;
            this.hospital.name = hospitalObject.name;
            this.hospital.address = hospitalObject.address;
        }

        shareService.broadcastDiagnosis = function() {
            $rootScope.$broadcast('handleDiagnosisBroadcast');
        };

        shareService.broadcastConcept = function() {
            $rootScope.$broadcast('handleConceptBroadcast');
        };

        shareService.broadcastSpecialty = function() {
            $rootScope.$broadcast('handleSpecialtyBroadcast');
        };

        shareService.broadcastDoctor = function() {
            $rootScope.$broadcast('handleDoctorBroadcast')
        };

        shareService.broadcastHospital = function() {
            $rootScope.$broadcast('handleHospitalBroadcast')
        };

        shareService.broadcastCancelDiagnosis = function() {
            $rootScope.$broadcast('handleCancelDiagnosisBroadcast');
        };

        shareService.broadcastCancelConcept = function() {
            $rootScope.$broadcast('handleCancelConceptBroadcast');
        };

        shareService.broadcastCancelSpecialty = function() {
            $rootScope.$broadcast('handleCancelSpecialtyBroadcast');
        };

        shareService.broadcastCancelDoctor = function() {
            $rootScope.$broadcast('handleCancelDoctorBroadcast')
        };

        shareService.broadcastCancelHospital = function() {
            $rootScope.$broadcast('handleCancelHospitalBroadcast')
        };
        

        return shareService;
    });
