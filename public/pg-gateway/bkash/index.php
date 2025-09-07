<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Bkash Payment</title>
    <meta name="viewport" content="width=device-width" ,="" initial-scale="1.0/">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrom=1">
    <script src="js/jquery-1.8.3.min.js"></script>
    <!-- <script id = "myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script> -->
    <script id = "myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script>
</head>

<body>
    <button id="bKash_button" style="display:none;">Pay With bKash</button>
    <script type="text/javascript">
        $(document).ready(function(){
            var request_token='<?php echo $_GET["token"]?>';
            var request_amount="0";
            var request_invoice="";

            var paymentID="";

            $.ajax({
                url: "get-payment-info.php?token="+request_token,
                type: 'POST',
                contentType: 'application/json',
                success: function (data) 
                {
                    var getData = JSON.parse(data);
                    if(getData.status=="success")
                    {
                        request_amount=getData.amount;
                        request_invoice=getData.invoice;



                        var accessToken='';
                        $(document).ready(function(){
                            $.ajax({
                                url: "token.php",
                                type: 'POST',
                                contentType: 'application/json',
                                success: function (data) 
                                {
                                    console.log('got data from token  ..');
                                    console.log(JSON.stringify(data));
                                    accessToken=JSON.stringify(data); 
                                    $("#bKash_button").trigger('click');
                                },
                                error: function()
                                {
                                  window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                              }
                          });

                            var paymentConfig={
                                createCheckoutURL:"createpayment.php",
                                executeCheckoutURL:"executepayment.php",
                                queryCheckoutURL:"paymentQuery.php",
                                searchCheckoutURL:"search.php",
                            };


                            var paymentRequest;
                            paymentRequest = { amount:request_amount,invoice:request_invoice,intent:'sale'};
                            console.log(JSON.stringify(paymentRequest));

                            bKash.init({
                                paymentMode: 'checkout',
                                paymentRequest: paymentRequest,
                                createRequest: function(request){
                                    console.log('=> createRequest (request) :: ');
                                    console.log(request);

                                    $.ajax({
                                        url: paymentConfig.createCheckoutURL+"?amount="+paymentRequest.amount+"&invoice="+paymentRequest.invoice+"&accessToken="+accessToken,
                                        type:'GET',
                                        contentType: 'application/json',
                                        success: function(data) {
                                            console.log('got data from create  ..');
                                            console.log('data ::=>');
                                            console.log(JSON.stringify(data));
                                            var createResponse = JSON.parse(data); 

                                            if(data && createResponse.paymentID != null)
                                            {
                                                paymentID = createResponse.paymentID;
                                                var amount = createResponse.amount;
                                                var merchantInvoiceNumber = createResponse.merchantInvoiceNumber;
                                                $.ajax({
                                                    url: "send-payment-create-response.php?token="+request_token+"&paymentID="+paymentID+"&amount="+amount+"&merchantInvoiceNumber="+merchantInvoiceNumber,
                                                    type: 'POST',
                                                    contentType: 'application/json',
                                                    success: function (data) 
                                                    {

                                                        var getData2 = JSON.parse(data);
                                                        if(getData2.status=="success")
                                                        {                                                            

                                                        }
                                                        else
                                                        {
                                                            window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                                        }

                                                    },
                                                    error: function()
                                                    {
                                                      window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                                  }  
                                              });

                                                bKash.create().onSuccess(createResponse);
                                            }
                                            else 
                                            {
                                                console.log('error');
                                                bKash.create().onError();
                                                window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                            }   
                                        },
                                        error: function(){
                                          console.log('error');
                                          bKash.create().onError();
                                          window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                      }
                                  });
                                },
                                executeRequestOnAuthorization: function()
                                {
                                    console.log('=> executeRequestOnAuthorization');
                                    $.ajax(
                                    {
                                        url: paymentConfig.executeCheckoutURL+"?paymentID="+paymentID+"&accessToken="+accessToken,
                                        type: 'GET',
                                        contentType:'application/json',
                                        success: function(data)
                                        {
                                            console.log('got data from execute  ..');
                                            console.log('data ::=>');
                                            console.log(JSON.stringify(data));
                                            data = JSON.parse(data);
                                            if(data && data.paymentID != null)
                                            {
                                             window.open('https://api.paystation.com.bd/public/bkash-payment-complete-response/'+request_token+'/'+data.paymentID+'/'+data.amount+'/'+data.merchantInvoiceNumber+'/'+data.trxID+'/'+data.transactionStatus+'/'+data.updateTime,'_self');
                                         }
                                         else 
                                         {
                                            if(data.errorCode == 2029)
                                            {
                                             window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                         }
                                         bKash.execute().onError();
                                         window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                     }
                                 },
                                 error: function()
                                 {
                                    bKash.execute().onError();
                                    window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
                                }
                            });
                                }
                            });
console.log("Right after init ");
});

}
else
{
    window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
}
},
error: function()
{
  window.open('https://api.paystation.com.bd/public/payment-request-failed/'+request_token,'_self');
}
});




});




function saveBkashRepose(sendData) 
{
    var fdata = JSON.parse(sendData);
    $.post('https://shl.com.bd/api/appapi/bkash-marchant-callback-url', {'data': fdata}, function(data, textStatus, xhr){});
}

function callReconfigure(val)
{
    bKash.reconfigure(val);
}

function clickPayButton()
{
    $("#bKash_button").trigger('click');
}


let searchParams = new URLSearchParams(window.location.search)
let param = searchParams.get('sent')
if(param == "app")
{
    setTimeout(function(){
       $("#bKash_button").trigger('click');
   },5000);

}

</script>

</body>
</html>
