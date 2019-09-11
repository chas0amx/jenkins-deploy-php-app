 <?php
    error_reporting(E_ALL);
    echo '<a href="/">Main Page v0.0.1</a><br/><br/>';
    
    function apiCall($url)
    {
      $token = file_get_contents('/var/run/secrets/kubernetes.io/serviceaccount/token'); 
      $url = "https://". $_SERVER['KUBERNETES_SERVICE_HOST'].":".$_SERVER['KUBERNETES_PORT_443_TCP_PORT']."/api/v1/".$url;
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
        ));
      $data = curl_exec($ch);
      $info = curl_getinfo($ch);

      if(curl_errno($ch)){
            throw new Exception(curl_error($ch));
      }
      return $data;
    }
    function parseResult($data){
      return $data;
    }

    $url = $_SERVER['REQUEST_URI'];
    $nameSpace=$pod='';
    $ee = parse_url($url);
    $query = !empty($ee['query'])?$ee['query']:'';
    $ee = trim($ee['path'], '/');
    $ee = explode('/', $ee);
    if(!empty($ee[0]))  $nameSpace = $ee[0];
    if(!empty($ee[1])) $pod = $ee[1];

    
    // get logs
    if($pod) {
        echo "Logs:<br/>\n";
        var_dump(nl2br(apiCall("namespaces/$nameSpace/pods/$pod/log/?".$query)));
        exit;
    } 

    // get pods list
    if($nameSpace) {
        echo "Pod list:<br/>\n";
        $items = json_decode(apiCall("namespaces/$nameSpace/pods"));
        if( count($items->items) ) {
          foreach($items->items as $item) {
            if(count($item->spec->containers)) {
              foreach($item->spec->containers as $cc) {
                echo '- <a href="/' . $item->metadata->namespace .'/'. $item->metadata->name . '/?container='.$cc->name.'">' . $item->metadata->name  . ' ('.$cc->name.')'."</a><br/>\n";
              }
            }
            else {
              echo '- <a href="/' . $item->metadata->namespace .'/'. $item->metadata->name . '">' . $item->metadata->name  . "</a><br/>\n";
            }
          }
        }
        exit;
    }

    // get namespaces
    echo "NameSpaces:<br/>\n";
    $items = json_decode(apiCall("namespaces/"));
    if( count($items->items) ) {
      foreach($items->items as $item) {
        echo '- <a href="/' . $item->metadata->name. '">' . $item->metadata->name  . "</a><br/>\n";
      }
    }
