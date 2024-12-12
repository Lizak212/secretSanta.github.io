<!DOCTYPE html>
<html>
<head>
   <title>Secret Santa</title>

   <style>
      body {
         display: flex; 
         flex-direction: column;
         align-items: center;
         justify-content: center;
      }
      table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 20px;
       }
       table, th, td {
         border: 1px solid #ddd;
       }
       th, td {
         padding: 12px;
         text-align: left;
       }
       th {
         background-color: #f2f2f2;
       }
       tr {
         background-color: #f9f9f9;
       }
   </style>
</head>

<body>
   <h1>Secret Santa</h1>

   <?php
      $db = new SQLite3("secret.db");

   $db->exec("
        CREATE TABLE IF NOT EXISTS participants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            giver_id INTEGER,
            receiver_id INTEGER,
            FOREIGN KEY (giver_id) REFERENCES participants(id),
            FOREIGN KEY (receiver_id) REFERENCES participants(id)
        )
    ");

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         if (isset ($_POST['add_participant'])) {
            $name = $_POST['participant_name']; 
            $email = $_POST['participant_email'];
            $db->exec ("INSERT INTO participants (name, email) VALUES ('$name', '$email')");
         }
      }
   ?>

   <h2>Add participant</h2>
   <form method = "POST">
      <label> Name:</label>
      <input type = "text" name = "participant_name">

      <label>Email: </label>
      <input type = "text" name = "participant_email">

      <button type = "submit" name = "add_participant">Submit</button>
   </form>

   <form method = "POST">
      <button type = "submit" name = "generate_pairs">Generate</button>      
   </form>

   <?php
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['generate_pairs'])) {
      $db->exec("DELETE FROM assignments");
      $res = $db->query ("SELECT id FROM participants");
      $pairs = [];

      while ($row = $res->fetchArray (SQLITE3_ASSOC)) {
         $pairs[] = $row['id'];
      }

      shuffle ($pairs);
      $length = count ($pairs);

      for ($i = 0; $i < $length; $i++){
         $giver = $pairs[$i];
         $receiver = $pairs[($i + 1) % $length];

         $db->exec ("INSERT INTO assignments (giver_id, receiver_id) VALUES ($giver, $receiver)");
      }
      $result = $db->query("
           SELECT 
               givers.name AS giver_name, 
               receivers.name AS receiver_name 
           FROM assignments
           JOIN participants AS givers ON assignments.giver_id = givers.id
           JOIN participants AS receivers ON assignments.receiver_id = receivers.id
       ");

      echo "<table>";
      echo "<tr>";
      echo "<th>Givers Name</th>";
      echo "<th>Recievers Name</th>";
      echo "</tr>";
   
      while ($row = $result->fetchArray (SQLITE3_ASSOC)){
         echo "<tr>";
         echo "<td>" . $row['giver_name'] . "</td>";
         echo "<td>" . $row['receiver_name'] . "</td>";
         echo "</tr>";
      }
      
      echo "</table>";
   }
   $db->close ();
   ?>
   
</body>
</html>
  
