var JSONP=function(){function i(e,n){var i=document.createElement("script"),s=false;i.src=e;i.async=true;var o=n||r.error;if(typeof o==="function"){i.onerror=function(t){o({url:e,event:t})}}i.onload=i.onreadystatechange=function(){if(!s&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){s=true;i.onload=i.onreadystatechange=null;if(i&&i.parentNode){i.parentNode.removeChild(i)}}};if(!t){t=document.getElementsByTagName("head")[0]}t.appendChild(i)}function s(e){return encodeURIComponent(e)}function o(t,o,u,a){var f=(t||"").indexOf("?")===-1?"?":"&",l;a=a||r["callbackName"]||"callback";var c=a+"_json"+ ++e;o=o||{};for(l in o){if(o.hasOwnProperty(l)){f+=s(l)+"="+s(o[l])+"&"}}n[c]=function(e){u(e);try{delete n[c]}catch(t){}n[c]=null};i(t+f+a+"="+c);return c}function u(e){r=e}var e=0,t,n=this,r={};return{get:o,init:u}}()

var vestatoken = vestatoken ? vestatoken : {};

vestatoken.init = function(request_params)
{
	vestatoken.ServiceURL = request_params.ServiceURL;
	vestatoken.AccountName = request_params.AccountName;
}
										  
										  
vestatoken._sendMessage = function(request_params)
{
   var payload         = request_params.payload         ? request_params.payload         : {};
   var onSuccess       = request_params.onSuccess       ? request_params.onSuccess       : function(){};
   var onFailed        = request_params.onFailed        ? request_params.onFailed        : function(){};
   var onInvalidInput  = request_params.onInvalidInput  ? request_params.onInvalidInput  : function(){};

   var url             = vestatoken.ServiceURL + '/' + request_params.message;

   payload['AccountName'] = vestatoken.AccountName;
   JSONP.init(
   {
         error: function(ex)
         {
            onFailed("Failed to load: " + ex.url);
         }
   });
   JSONP.get(url, payload, function(data)
   {
         if ( !data.ResponseCode )
         {
            onFailed('Unknown System Failure');
         }
         else if( data.ResponseCode >= 500 )
         {
            onInvalidInput('ResponseCode:'+data.ResponseCode + '\n' + data.ResponseText);
         }
         else if( data.ResponseCode != 0 )
         {
            onFailed('ResponseCode:'+data.ResponseCode + '\n' + data.ResponseText);
         }
         else
         {
            onSuccess(data);
         }
    });
   
}

//Should be in a seperate file
vestatoken.getcreditcardtoken = function(request_params)
{
   var creditcardnumber    = request_params.ChargeAccountNumber;
   var userOnSuccess       = request_params.onSuccess ? request_params.onSuccess : function(){};
   var userOnFailed        = request_params.onSuccess ? request_params.onFailed  : function(){};
   var userOnInvalidInput  = request_params.onSuccess ? request_params.onInvalidInput  : function(){};

   if ( ! vestatoken._isNumeric(creditcardnumber) )
   {
      userOnInvalidInput('Charge Account Number must be numeric.');
      return;
   }

   if ( creditcardnumber.length < 13 )
   {
      userOnInvalidInput('Charge Account Number must be at least 13 digits.');
      return;
   }

   if ( ! vestatoken._isMod10(creditcardnumber) )
   {
      userOnInvalidInput('Charge Account Number is not valid. Please verify.');
      return;
   }

   vestatoken._sendMessage(
   { message        : 'ChargeAccountToTemporaryToken'
   , payload        : {ChargeAccountNumber : creditcardnumber}
   , onSuccess      : userOnSuccess
   , onFailed       : userOnFailed
   , onInvalidInput : userOnInvalidInput
   });
}

/* Added for APA 4247 */
vestatoken.getchecktoken = function(request_params)
{
   var checkAccountNumber  = request_params.CheckAccountNumber;
   var userOnSuccess       = request_params.onSuccess ? request_params.onSuccess : function(){};
   var userOnFailed        = request_params.onSuccess ? request_params.onFailed  : function(){};
   var userOnInvalidInput  = request_params.onSuccess ? request_params.onInvalidInput  : function(){};
   
   if ( ! vestatoken._isNumeric(checkAccountNumber) )
   {
      userOnInvalidInput('Check Account Number must be numeric.');
      return;
   }

   if ( checkAccountNumber.length < 4 || checkAccountNumber.length > 17 )
   {
      userOnInvalidInput('Check Account Number must be between 4 and 17 digits long.');
      return;
   }

   vestatoken._sendMessage(
   { message        : 'BankAccountToTemporaryToken'
   , payload        : {CheckAccountNumber : checkAccountNumber}
   , onSuccess      : userOnSuccess
   , onFailed       : userOnFailed
   , onInvalidInput : userOnInvalidInput
   });
};

vestatoken._isNumeric = function(value)
{
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) return false;
  return true;
}

vestatoken._isMod10 = function(val)
{
   var iTotal = 0;
   var parity = val.length % 2;

   for (var i=0; i<val.length; i++)
   {
      var calc = parseInt(val.charAt(i));
      if (i % 2 == parity) calc = calc * 2;
      if (calc > 9)        calc = calc - 9;
      iTotal += calc;
   }

   if ((iTotal%10)==0) return true;

   return false;
}