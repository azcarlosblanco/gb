angular
.module('ngNova')
.config(['ChartJsProvider', function (ChartJsProvider) {
    // Configure all charts
    ChartJsProvider.setOptions({
      responsive: true,
      maintainAspectRatio: false,
      //colors : [ '#DCDCDC', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360', '#803690', '#00ADF9']
      colors : ['rgb(31, 119, 180)','rgb(174, 199, 232)','rgb(255, 127, 14)','rgb(255, 187, 120)','rgb(44, 160, 44)','rgb(119, 119, 255)','rgb(152, 223, 138)','rgb(214, 39, 40)']
    });
    // Configure all line charts scaleUse2Y
    ChartJsProvider.setOptions('Line', {
      datasetFill: false,
      legend: {
            display: false
        },
      tooltipTemplate: "<%= value%>",
      multiTooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>%"
    });
    ChartJsProvider.setOptions('doughnut', {
      cutoutPercentage : 80,
      segmentShowStroke: false,
      animation: {
        animationRotate: false,
        easing: 'easeOutQuad'
      }
    });
  }])
.controller("DashboardCtrl", ['typedashboard','emissionavgtimeresp', 'employeesresp', 'salesbymonthresp', 'agentsalesresp', '$scope', '$timeout', 'Restangular', '$state', '$stateParams', '__env', '$http',
  function (typedashboard,emissionavgtimeresp, employeesresp, salesbymonthresp, agentsalesresp, $scope, $timeout, Restangular, $state, $stateParams, __env, $http) {
        //console.log(listapo);

      $scope.typedashboard = typedashboard.type;
      $scope.today = new Date();

      $scope.colors = ['rgb(31, 119, 180)','rgb(174, 199, 232)','rgb(255, 127, 14)','rgb(255, 187, 120)','rgb(44, 160, 44)','rgb(119, 119, 255)','rgb(152, 223, 138)','rgb(214, 39, 40)'];

      $scope.avgtimeemission = null;
      $scope.employees = employeesresp.data.data;

      /*var months = {"1":"Enero",
                     "2":"Febrero",
                     "3":"Marzo",
                     "4":"April",
                     "5":"Mayo",
                     "6":"Junio",
                     "7":"Julio",
                     "8":"Agosto",
                     "9":"Septiembre",
                     "10":"Octubre",
                     "11":"Noviembre",
                     "12":"Diciembre",
                    };*/
      var months = ["Enero",
                    "Febrero",
                    "Marzo",
                    "April",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre",
                    ];

      $scope.widget = null;
      $scope.showWidgetSettings = false;
      
      //TIME USE IN EACH PROCESS TO EMIT A POLICY
      // use slice() to copy the array and not just make a reference
      $scope.emsionespdata = emissionavgtimeresp.data.data.slice(0);
      $scope.emsionespdata.sort(function(a,b) {
          return b.time - a.time;
      });
      var edataset = [];
      var elabels = [];
      for(x in $scope.emsionespdata){
        edataset.push($scope.emsionespdata[x]['time']);
        elabels.push($scope.emsionespdata[x]['name']);
      }
      console.log(emissionavgtimeresp);

      $scope.avgtimeemission = {
        'responsible': "",
        "params": {"to":"","from":"","responsible":"","responsible_id":""},
        "title": "Tiempo Promedio Emitir una Póliza",
        "url": "dashboard/procedureTime",
        "name": "avgtimeemission",
        "responsible": true,
      }

      //RESUME OF NEW POLICIES SOLD IN A PERIOD OF TIME, BY DEFAULT THE LAST MONTH
      $scope.salesbymonth = {
        'data': [],
        'labels': [],
        'series': ["2015","2016"],
        'options': {
          'legend': {
            display: true,
          }
        },
        'colors':[{
                    'backgroundColor': 'rgb(255, 127, 14)',
                    'borderColor': 'rgb(255, 127, 14)',
                }],
        "params": {"to":"","from":""},
        "title": "Ventas Pólizas Nuevas",
        "url": "dashboard/policiesSales",
        "name": "salesbymonth",
        "responsible": false,
        "total_current":0,
        "total_past":0,
      }

      $scope.salesmonth = {
        "title": "Ventas del Mes",
        'value': 0,
        'comp_percentage': 0,
        'increase': true,
        'decrease': false,
        'comp_percentage': 12,
        'lastmonth_value': 0,
        "name": "salesmonth",
      }

      // use slice() to copy the array and not just make a reference
      var salesbymonthdata = salesbymonthresp.data.data;
      var tmpcurrentdata = [];
      var tmplastdata = [];
      var currentMonth = $scope.today.getMonth();
      //get months from current month to december
      monthssales = [];
      for(var i=currentMonth+1;i<12;i++){
        monthssales.push(i);
      }
      for(var i=0;i<currentMonth+1;i++){
        monthssales.push(i);
      }
      

      for(x in monthssales){
        var indexmonth = parseInt(monthssales[x])+1;
        if(salesbymonthdata['current'][indexmonth]==undefined){
          tmpcurrentdata.push(0.00);
        }else{
          tmpcurrentdata.push(salesbymonthdata['current'][indexmonth]);
        }
        if(salesbymonthdata['past'][indexmonth]==undefined){
          tmplastdata.push(0.00);
        }else{
          tmplastdata.push(salesbymonthdata['past'][indexmonth]);
        }
        $scope.salesbymonth.labels.push(months[monthssales[x]]);
        $scope.salesbymonth.total_current+=tmpcurrentdata[tmpcurrentdata.length-1];
        $scope.salesbymonth.total_past+=tmplastdata[tmplastdata.length-1];
      }
      $scope.salesbymonth.data=[tmplastdata,tmpcurrentdata];

      //data of current month
      $scope.salesmonth.value = tmpcurrentdata[tmpcurrentdata.length-1];
      $scope.salesmonth.lastmonth_value = tmpcurrentdata[tmpcurrentdata.length-2];
      if($scope.salesmonth.value >= $scope.salesmonth.lastmonth_value){
        $scope.salesmonth.increase = true;
        $scope.salesmonth.decrease = false;
        $scope.salesmonth.comp_percentage = ($scope.salesmonth.lastmonth_value / $scope.salesmonth.value)*100;
      }else if($scope.salesmonth.value < $scope.salesmonth.lastmonth_value){
        $scope.salesmonth.increase = false;
        $scope.salesmonth.decrease = true;
        $scope.salesmonth.comp_percentage = ($scope.salesmonth.value / $scope.salesmonth.lastmonth_value)*100;
      }

      $scope.salesagent = {
        'data': [],
        'labels': [],
        'series': ["Ventas"],
        /*'options': {
          'legend': {
            display: true,
          }
        },*/
        'colors':[{
                    'backgroundColor': 'rgb(119, 119, 255)',
                    'borderColor': 'rgb(119, 119, 255)',
                }],
        "params": {"to":"","from":""},
        "title": "Ventas Pólizas Nuevas",
        "url": "dashboard/agentSales",
        "name": "salesagent",
        "responsible": false
      }

      var salesAgent = agentsalesresp.data.data.sales;
      var agents = agentsalesresp.data.data.agents;
      var tmpagentsales = []
      for(x in salesAgent){  
        tmpagentsales.push(salesAgent[x]);
        $scope.salesagent['labels'].push(agents[x]);
      }
      $scope.salesagent['data'].push(tmpagentsales);


      $scope.openSettings = function(chartObj){
        $scope.widget = chartObj;
        $scope.showWidgetSettings = true;
      }

      $scope.closeWidgetSettings = function(){
        $scope.showWidgetSettings = false;
      }

      $scope.updateWidget = function(chartObj){
        if(chartObj.name=="avgtimeemission"){
          if(chartObj.params.responsible!=undefined && chartObj.params.responsible.user_id!=undefined){
            chartObj.params.responsible_id=chartObj.params.responsible.user_id;
          }else{
            chartObj.params.responsible_id="";
          }
          makeDataRequest(chartObj);
        }

        if(chartObj.name=="newpoliciesresume"){

        }

        if(chartObj.name=="salesagent"){
          makeDataRequest(chartObj);
        }

      }

      function reqEmissionAvgTime(chartObj){

      } 

      function makeDataRequest(chartObj){
        $scope.showCreateCall= true;
        Restangular
        .one(chartObj.url)
        .get(chartObj.params)
        .then(
            function successCallback(response) {
              $scope.showWidgetSettings = false;
              $scope.checking_graph = false;
              if(chartObj.name=="avgtimeemission"){
                $scope.emsionespdata = response.data.data
                $scope.emsionespdata.sort(function(a,b) {
                  return b.time - a.time;
                });
              }else if(chartObj.name=="salesagent"){
                var salesAgent = response.data.data.sales;
                var agents = response.data.data.agents;
                var tmpagentsales = [];
                $scope.salesagent['labels'] = [];
                $scope.salesagent['data'] = [];
                for(x in salesAgent){  
                  tmpagentsales.push(salesAgent[x]);
                  $scope.salesagent['labels'].push(agents[x]);
                }
                $scope.salesagent['data'].push(tmpagentsales);
              }
            },
            function errorCallback(response) {
              $scope.checking_graph = false;
            }
        );
      }
}]);