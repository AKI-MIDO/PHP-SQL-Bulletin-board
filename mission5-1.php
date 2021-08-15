<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>Bulletin board</title>
  </head>
  <body>

<?php
    //Notice-を非表示にする
    error_reporting(E_ALL & ~E_NOTICE);
       
    //データベースに接続するための準備
    $dsn='データベース';
    $user = 'ユーザー名';
    $password = 'パスワード';
    
    //実際にデータベースに接続tryを使うことで未然にエラーを処理できる
    try{
        
        //この構文で接続して接続できたらechoを表示arryからはsqlでエラーが起こった時の処理を意味する
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        echo "データベースに接続できました<br>";
        
        
    }catch(PDOException $e){
        
        //接続できなかった時にこの処理をする
        echo "データベースに接続できませんでした",$e->getMessage();
    }
    
    //デーブルを作成する
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
    ."("
    //投稿番号AUTO〜の部分で自動的に連番になる
    ."id INT AUTO_INCREMENT PRIMARY KEY,"
    
    //名前とコメントと日付とパスワードの保存場所
    ."name char(32),"
    ."comment TEXT,"
    ."date DATETIME,"
    ."pass TEXT"
    .");";
    
    //これはクエリ
    $stmt = $pdo->query($sql);
    
    //データベースのテーブル一覧を表示
    //$sql ='SHOW CREATE TABLE tbtest';
    //$result = $pdo -> query($sql);
    //foreach ($result as $row){
        //echo $row[1];
    //}
    //echo "<hr>";
    
    
    $name=$_POST['name'];
    $comment=$_POST['comment'];
    $date=date("Y-m-d H-i-s");
    $pass=$_POST['password'];
    $id=$_POST['editNO'];
    //INSERT命令の準備
    $sql = $pdo -> prepare("INSERT INTO tbtest (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
    //insertの中身
    if(!empty($_POST['name']) && (!empty($_POST['comment']))){
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
    $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
    
    //bindparamの中身の変数の定義
    
    $sql -> execute();
    }
    
    if(!empty($_POST['name']) && !empty($_POST['comment'])){
        
    
        if(empty($hidden_num)){
        // もし編集対象番号（後で消される欄）に数字が入っていなければ
            $sql = $pdo -> prepare("INSERT INTO tbtest (name,comment,pass,date) VALUES (:name, :comment, :pass, :date)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> execute();
        
        }else{
        
            $sql='SELECT * FROM tbtest';
            $stmt = $pdo->query($sql);
            $lines = $stmt->fetchAll();
            foreach($lines as $line){
                if($hidden_num==$line["id"]){
        // 編集対象番号（後で消される欄）の数字とデータベースのid（投稿番号）が一致したら
                    $sql = 'UPDATE tbtest SET name=:name,comment=:comment,pass=:pass WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment',  $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->execute();
                }
                
            }
            
        }
        
    }
    

    
    
    //削除機能ー削除番号が空ではない時に
    if(!empty($_POST["dnum"]) && !empty($_POST["dpassword"])){
        //削除の時に使う変数（削除番号とパスワード)
        $id = $_POST["dnum"];
        $dpassword=$_POST["dpassword"];

        $sql='SELECT * FROM tbtest';
        $stmt=$pdo->query($sql);
        $lines=$stmt->fetchAll();
        foreach($lines as $line){

            //投稿番号と削除番号が一致してパスワードと削除のパスワードが一致するとき
            if($id==$line["id"] && $dpassword==$line["pass"]){
                
                //送信された削除番号をidに代入
                $sql = 'delete from tbtest where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    } 

    //編集機能
    if(!empty($_POST["edit"])&&!empty($_POST["epassword"])){
        $id = $_POST["edit"]; 
        $epassword = $_POST["epassword"];
        $sql = 'SELECT * FROM tbtest';
        $stmt = $pdo->query($sql);
        $lines = $stmt->fetchAll();
        
        // データベースの中身を全て取り出す
            foreach($lines as $line){
            
                //   データベースの中身を一行ずつ読み込む     
                if($epassword==$line["pass"]){
                    if($id==$line["id"]){
                        $e_num=$line["id"];
                        $e_name=$line["name"];
                        $e_comment=$line["comment"];
                        $e_password=$line["pass"]; 
                    }
                }
            }
    }
    
    
    ?>
    <p>【　投稿フォーム　】</p>
    <form action="mission5-1.php" method="post">
      <input type="text" name="name" placeholder="名前" value="<?php if(!empty($e_name)) {echo $e_name;} ?>"><br>
      <input type="text" name="comment" placeholder="コメント" value="<?php if(!empty($e_comment)) {echo $e_comment;} ?>"><br>
      <input type="hidden" name="editNO" value="<?php if(!empty($e_num)) {echo $e_num;} ?>">
      <input type="text" name="password" placeholder="パスワード" value="<?php if(!empty($e_password)) {echo $e_password;} ?>">
      <input type="submit" name="submit" value="送信">
    </form><br>
    
    <p>【　削除フォーム　】</p>
    <form action="mission5-1.php" method="post">
      <input type="text" name="dnum" placeholder="削除対象番号"><br>
      <input type="text" name="dpassword" placeholder="パスワード"> 
      <input type="submit" name="delete" value="削除">
    </form><br>
    
    <p>【　編集フォーム　】</p>
    <form action="mission5-1.php" method="post">
      <input type="text" name="edit" placeholder="編集対象番号"><br>
      <input type="text" name="epassword" placeholder="パスワード">
      <input type="submit" value="編集">
    </form><br>
    <?php
    //表示機能
     $sql = 'SELECT * FROM tbtest';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].',';
            echo $row['name'].',';
            echo $row['comment'].',';
            echo $row['date'].',';
            echo $row['pass'].'<br>';
        echo "<hr>";
        }
    ?>
  </body>
</html>