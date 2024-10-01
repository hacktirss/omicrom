<?php

function impletras($nmr, $etq){

  $u = array("","un","dos","tres","cuatro","cinco","seis","siete","ocho","nueve","diez","once","doce","trece","catorce",
          "quince","dieciseis","diecisiete","dieciocho","diecinueve","veinte","veintiun","veintidos","veintitres","veinticuatro",
          "veinticinco","veintiseis","veintisiete","veintiocho","veintinueve");

  $d = array("","diez","veinte","treinta","cuarenta","cincuenta","sesenta","setenta","ochenta","noventa");

  $c = array("","ciento","doscientos","trescientos","cuatrocientos","quinientos","seiscientos","setecientos","ochocientos","novecientos");

  $s = array("","mil","billon","mil","millon","mil","","");

  $p = array("","mil","billones","mil","millones","mil","","");

  $e = array(""," y ","cien"," "," menos","cero"," de ");

  $et  = $etq;

  if($nmr<0){$nm=-$nmr;}else{$nm=$nmr;}
  //nm  = iif( nmr < 0, -nmr, nmr )

  $n=((int)$nm);
  //n     = int(nm)

  $ndc = round(100*($nm-$n));
  //ndc = round(100*(nm-n),0)

  if($ndc > 0){$dc=substr($ndc,0,2)."/100";}else{$dc="";}
  //dc  = iif( ndc > 0, str(ndc,2,0) + "/100", "" )

  $y = ((string) $n );
  for($i = strlen($y); $i < 18; $i++){
      $y = " ".$y;
   }

  //y     = str(n,18,0)

  if($n < 2){
     //if n < 2

     if( $n == 0){$z=$e[3] . $e[5];}else{$z=$e[3] . $u[1];}
     //z = iif( n = 0, e[3] + e[5], e[3] + u[1] )

  }else{

     $z = "";
     //z = ""
     $k = 1;
     //k = 1

     while ($k < 7){
        //do while k < 7


        $x = substr($y,($k-1)*3+0,3);
      
        //x = substr(y,(k-1)*3+1,3)
       $x = (int)$x;
        $n = $x*1;
        //n = val(x)     Convierte de string a numero


        if ($n > 0){
           //if n > 0


           if($n == 1){$q=$e[3] . $s[$k];}else{ $q=$e[3] . $p[$k];}
           //q = iif( n = 1, e[3] + s[k], e[3] + p[k] )

           
           if($n == 100){
             //if n = 100
             $r = $e[2];
             
           }else if($nmr < 100){
              $n = substr($x,0,1)*1;
              //n = val(substr(x,1,1))

              if($n > 0){$r = $d[$n].$e[3];}else{$r='';}

              //r = iif( n > 0, c[n] + e[3], "" )


              $n = substr($x,1,2)*1;


              //n = val(substr(x,2,2))

              if($n > 0){

                 //if n > 0
                 if($n < 30){
                    //if n < 30
                    $t = $u[$n];
                 }else{
                    $n = substr($x,2,1)*1;
                    //n = val(substr(x,3,1))
                    if($n > 0){$t = $e[1] . $u[$n];}else{$t="";}
                    //t = iif( n > 0, e[1] + u[n], "" )
                    $tmp=substr($x,1,1);
                    $tmp=$tmp*1;
                    $t = $d[$tmp] . $t;
                    //t = d[val(substr(x,2,1))] + t
                 }
              }else{
                 $t = "";
              }
              $r = $r . $t;
           }else{
              $n = substr($x,0,1)*1;
              //n = val(substr(x,1,1))

              if($n > 0){$r = $c[$n].$e[3];}else{$r='';}

              //r = iif( n > 0, c[n] + e[3], "" )

               
              $n = (int) substr($x,1,2)*1;


              //n = val(substr(x,2,2))

              if($n > 0){

                 //if n > 0
                 if($n < 30){
                    //if n < 30
                    $t = $u[$n];
                 }else{
                    $n = substr($x,2,1)*1;
                    //n = val(substr(x,3,1))
                    if($n > 0){$t = $e[1] . $u[$n];}else{$t="";}
                    //t = iif( n > 0, e[1] + u[n], "" )
                    $tmp=substr($x,1,1);
                    $tmp=$tmp*1;
                    $t = $d[$tmp] . $t;
                    //t = d[val(substr(x,2,1))] + t
                 }
              }else{
                 $t = "";
              }

              $r = $r . $t;
        }

        $z = $z . $e[3] . $r . $q;
      }

      $k++;

     }  //END DO

     $z = ltrim($z);
     //$z = trim(z)


     if(substr($z,strlen($z)-4,4)==substr($s[1],2,4) or substr($z,strlen($z)-6,6)==substr($p[1],2,6)){
         $z=$z+$e[6];
     }

     //z = iif((substr(z,len(z)-3,4)=substr(s[1],3,4)).or.(substr(z,len(z)-5,6)=substr(p[1],3,6)),z+e[6],z)
  }

  if($nmr < 0.00){
     $z = $e[4] . $z;
  }

  //z = iif( nmr < 0.00, e[4] + z, z )


  $NTX  = "***" . strtoupper(substr($z,0,1)) . substr($z,1,strlen($z)-1) . $e[3] . $et . $e[3] . $dc . " M.N.***";
  //  = upper(substr(z,2,1)) + substr(z,3,len(z)-2) + e[3] + et + e[3] + dc

  return $NTX;

}



?>