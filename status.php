<!doctype html>
<html lang="de">
  <head>

    <?php
      if(!$_POST["id"] || !$_POST["token"]) {
        exit;
      }
    ?>

    <title>Bad Dragon Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <style>
      body {
        background-color:#c1c2c5;
      }

      a {
        color:#c1c2c5;
      }

      a:hover {
        color:#c1c2c5;
      }

      .img-responsive{
        max-width:100%;
        max-height:200px;
      }
      .jumbotron{
        background-color:#25292e;
        color:#c1c2c5;
        border-radius:0px;
      }
      .intermediate{
        text-align:center;
        color:#25292e;
        font-weight:bold;
        display: none;
      }
      .list-group-item{
        background-color:#25292e
      }

      @keyframes loading {
          from {width:0%;}
          to {width:100%;}
      }

      .progress-bar {
          animation-name: loading;
          animation-duration: 3s;
      }

      #button {
        position: fixed;
        top: 0px;
        left: 0px;
      }

      #header {
        position: fixed;
        top: 0px;
        left: 0px;
        width: 100vw;
        height: 38px;
        background-color: #25292e;
        text-align: center;
      }

    </style>
  </head>
  <body>
    <div class="jumbotron text-center">
      <p id="statustext" style="display:none;">
        Detailed status for order #<span id="id"></span><br>
        from <span id="date"></span> (<span id="daysago"></span> days ago)
      </p>
      <hr>
      <div id="loading">
        <h1>Please wait...</h1>
        <div class="progress">
          <div class="progress-bar bg-danger" style="width: 0%"></div>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <h1 id="orderStatus.externalName"></h1>
          <p id="orderStatus.externalDesc"></p>
        </div>
        <div class="col">
          <p>
            <span id="firstname"></span> <span id="lastname"></span><br>
            <span id="address1"></span><br>
            <span id="zipcode"></span> <span id="city"></span>
          </p>
        </div>
      </div>
    </div>

    <p class="intermediate" id="howmanyitems">Your order contains <span id="allitems.length"></span> items.</p>

    <table class="table table-dark table-striped" id="customTable" style="display:none;">
      <thead>
        <tr>
          <th>Custom Toys</th>
          <th>Details</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="table1">
      </tbody>
    </table>
    <table class="table table-dark table-striped" id="invTable" style="display:none;">
      <thead>
        <tr>
          <th>Inventory Items</th>
          <th>Details</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="table2">
      </tbody>
    </table>


    <script>
      function reqListener () {
        console.log(this.responseText);
      }

      var oReq = new XMLHttpRequest();
      oReq.onload = function() {
        var getstring = "get.php?id="+"<?php echo $_POST["id"]; ?>"+"&token="+"<?php echo $_POST["token"]; ?>";
        $.getJSON(getstring, function(data) {
          if (data["error"]) {
            document.getElementById("loading").innerHTML="<strong>There was a problem:</strong><br>"+data["error"]+'<br><a href="index.html"><button type="button" class="btn btn-danger">Try again</button></a>';
          } else {
            document.getElementById("loading").style.display="none";
            document.getElementById("statustext").style.display="block";
            document.getElementById("howmanyitems").style.display="block";
            document.getElementById("id").innerHTML=data["id"];
            var date = new Date(data["created"]);
            var year = date.getFullYear();
            var month = withzero(date.getMonth()+1);
            var day = withzero(date.getDate());
            var h = withzero(date.getHours());
            var m = withzero(date.getMinutes());
            var s = withzero(date.getSeconds());
            var datestring = year+"-"+month+"-"+day+" at "+h+":"+m;
            var today = new Date();
            var timeDiff = Math.abs(today.getTime() - date.getTime());
            var daysago = Math.ceil(timeDiff / (1000 * 3600 * 24));
            document.getElementById("date").innerHTML=datestring;
            document.getElementById("daysago").innerHTML=daysago;
            document.getElementById("allitems.length").innerHTML=data["customToys"].length+data["inventoryItems"].length;
            document.getElementById("orderStatus.externalName").innerHTML=data["orderStatus"]["externalName"];
            document.getElementById("orderStatus.externalDesc").innerHTML=data["orderStatus"]["externalDesc"];
            document.getElementById("firstname").innerHTML=lower(data["shippingAddress"]["firstname"]);
            document.getElementById("lastname").innerHTML=lower(data["shippingAddress"]["lastname"]);
            document.getElementById("address1").innerHTML=lower(data["shippingAddress"]["address1"]);
            document.getElementById("zipcode").innerHTML=data["shippingAddress"]["zipcode"];
            document.getElementById("city").innerHTML=lower(data["shippingAddress"]["city"]);
            if (data["customToys"].length > 0){
              document.getElementById("customTable").style.display="table";
            }
            if (data["inventoryItems"].length > 0){
              document.getElementById("invTable").style.display="table";
            }
            for (var i=0;i<data["customToys"].length;i++) {
              var statuscode=data["customToys"][i]["status"];
              switch (statuscode) {
                case 3:
                var statusdesc = "Queued for production";
                break;
                case 16:
                var statusdesc = "Packing your order right now";
                break;
                case 23:
                var statusdesc = "Shipped";
                break;
                case 80:
                var statusdesc = "Cancelled";
                break;
                case 87:
                var statusdesc = "QA failed, will retry";
                break;
                case 90:
                var statusdesc = "Reserved in warehouse";
                break;
                case 4:
                var statusdesc = "In production + curing";
                break;
                case 11:
                var statusdesc = "Production finished, waiting to be moved to QA queue";
                break;
                case 12:
                var statusdesc = "In QA queue";
                break;
                case 13:
                var statusdesc = "QA underway";
                break;
                case 92:
                var statusdesc = "waiting to be inspected by QA";
                default:
                var statusdesc = "No description available";
              }

              if (data["customToys"][i]["cumtube"]==0){
                var cumtube='<li class="list-group-item">Without cumtube</li>';
              }
              else if (data["customToys"][i]["cumtube"]==1){
                var cumtube='<li class="list-group-item">With cumtube</li>';
              }
              else{
                var cumtube="";
              }

              if (data["customToys"][i]["suctioncup"]==0){
                var suctioncup='<li class="list-group-item">Without suction cup</li>';
              }
              else if (data["customToys"][i]["suctioncup"]==1){
                var suctioncup='<li class="list-group-item">With suction cup</li>';
              }
              else{
                var suctioncup="";
              }

              document.getElementById("table1").insertAdjacentHTML( 'beforeend', '<tr><td><a href="https://bad-dragon.com/products/'+data["customToys"][i]["sku"]+'" target="_blank"><strong>'+data["customToys"][i]["productName"]+'</strong><br><img src="'+data["customToys"][i]["imageURL"]+'" class="img-responsive voc_list_preview_img"></a></td><td><ul class="list-group"><li class="list-group-item">Size: '+data["customToys"][i]["size"]+'</li><li class="list-group-item">Firmness: '+data["customToys"][i]["firmness"]+'</li><li class="list-group-item">Color: '+data["customToys"][i]["color"]+'</li>'+cumtube+suctioncup+'</ul></td><td>'+data["customToys"][i]["status"]+'<br>'+statusdesc+'</td></tr>');
            }

            for (var j=0;j<data["inventoryItems"].length;j++) {
              var statuscodeInv=data["inventoryItems"][j]["status"];
              switch (statuscodeInv) {
                case 3:
                var statusdescInv = "Queued for production";
                break;
                case 16:
                var statusdescInv = "Packing your order right now";
                break;
                case 23:
                var statusdescInv = "Shipped";
                break;
                case 80:
                var statusdescInv = "Cancelled";
                break;
                case 87:
                var statusdescInv = "QA failed, will retry";
                break;
                case 90:
                var statusdescInv = "Reserved in warehouse";
                break;
                case 4:
                var statusdescInv = "In production + curing";
                break;
                case 11:
                var statusdescInv = "Production finished, waiting to be moved to QA queue";
                break;
                case 12:
                var statusdescInv = "In QA queue";
                break;
                case 13:
                var statusdescInv = "QA underway";
                break;
                case 92:
                var statusdescInv = "waiting to be inspected by QA";
                default:
                var statusdescInv = "No description available";
              }

              var details="";

              if(data["inventoryItems"][j]["sku"]=="cumlube" || data["inventoryItems"][j]["sku"]=="cumlubeclear"){
                var predetails = '<ul class="list-group">';
                var postdetails = '</ul>';
              }

              if (data["inventoryItems"][j]["sku"]=="cumlube"){
                var cumlubesize = '<li class="list-group-item">Size: '+data["inventoryItems"][j]["options"][0].value+'</li>';
                details = predetails + cumlubesize + postdetails;
              }

              document.getElementById("table2").insertAdjacentHTML( 'beforeend', '<tr><td><a href="https://bad-dragon.com/products/'+data["inventoryItems"][j]["sku"]+'" target="_blank"><strong>'+data["inventoryItems"][j]["productName"]+'</strong><br><img src="'+data["inventoryItems"][j]["imageURL"]+'" class="img-responsive voc_list_preview_img"></a></td><td>'+details+'</td><td>'+data["inventoryItems"][j]["status"]+'<br>'+statusdescInv+'</td></tr>');
            }
          }
        });
      };

      oReq.open("get", "get.php", true);
      oReq.send();

      function lower(string) {
        return string.replace(/\w\S*/g, function (word) {
          return word.charAt(0) + word.slice(1).toLowerCase();
        });
      }

      function withzero(number) {
        if(number<10){number="0"+number;}
        return number;
      }

    </script>

    <div id="header"><img src="https://i.imgur.com/wwazByf.png" height="100%"></div>
    <div id="button"><a href="index.html"><button type="button" class="btn btn-danger">Go back</button></a></div>

  </body>
</html>
