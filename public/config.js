var novaNotification = function ()
{
  return {
    $get: function (webNotification)
    {
      return {
        showNotification: function (data)
        {
			  webNotification.showNotification(data.title, {
				body: data.body,
				icon: 'assets/img/GB.ico',
				onClick: function onNotificationClicked() {
				    console.log('Notification clicked.');
				},
				autoClose: 4000 //auto close the notification after 4 seconds (you can manually close it via hide function)
			}, function onShow(error, hide) {
				if (error) {
				    window.alert('Unable to show notification: ' + error.message);
				} else {
				    console.log('Notification Shown.');
				    setTimeout(function hideNotification() {
				        console.log('Hiding notification....');
				        hide(); //manually close the notification (you can skip this if you use the autoClose option)
				    }, 5000);
				}
			});
        }
      };
    }
  };
};

var authenticationToken = function ()
{
  return {
    $get: function (__env)
    {
      return {
        token: function ()
        {
          var token = localStorage.getItem(__env.tokenst);
          if(token == undefined || token==""){
          	window.location.href=__env.frontUrl+"#/login";
          }else{
          	return token
          }
        }
      };
    }
  };
};