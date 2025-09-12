<?php
// animals_endpoint_debug.php - Debug database connection và query
header("Content-Type: text/html; charset=UTF-8");

echo "<h2>🔍 Debug Database Connection & Query</h2>";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'animals_3d';
    $username = 'root';
    $password = '';
    
    echo "<h3>1. Testing Database Connection...</h3>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br>";
    
    // Test basic tables
    echo "<h3>2. Testing Basic Tables...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Available tables: " . implode(', ', $tables) . "<br>";
    
    // Test animals_new table
    if (in_array('animals_new', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM animals_new");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ animals_new table: " . $result['count'] . " records<br>";
        
        // Show sample data
        if ($result['count'] > 0) {
            $stmt = $pdo->query("SELECT id, name, species_id FROM animals_new LIMIT 3");
            $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "📊 Sample animals_new data:<br>";
            echo "<pre>" . json_encode($sample, JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "❌ animals_new table not found<br>";
    }
    
    // Test animal_species table
    if (in_array('animal_species', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM animal_species");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ animal_species table: " . $result['count'] . " records<br>";
    } else {
        echo "❌ animal_species table not found<br>";
    }
    
    // Test animal_media table
    if (in_array('animal_media', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM animal_media");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ animal_media table: " . $result['count'] . " records<br>";
    } else {
        echo "❌ animal_media table not found<br>";
    }
    
    // Test the full query
    echo "<h3>3. Testing Full Query...</h3>";
    try {
        $query = "
            SELECT
                an.id,
                an.name,
                an.description,
                an.population_count,
                an.created_at,
                an.updated_at,
                
                asp.species_name,
                asp.scientific_name,
                asp.family,
                asp.order_name,
                asp.class_name,
                
                h.name AS habitat_name,
                h.description AS habitat_description,
                h.icon AS habitat_icon,
                
                cs.name AS conservation_status_name,
                cs.description AS conservation_status_description,
                cs.color_code AS conservation_status_color,
                cs.priority AS conservation_status_priority,
                
                cd.iucn_status,
                cd.population_trend,
                cd.threats,
                cd.conservation_actions,
                cd.last_assessment_date,
                
                (SELECT GROUP_CONCAT(CONCAT(am.file_url, '||', am.media_type, '||', am.is_primary) SEPARATOR ';;')
                 FROM animal_media am WHERE am.animal_id = an.id) AS media_data
            FROM
                animals_new an
            LEFT JOIN animal_species asp ON an.species_id = asp.id
            LEFT JOIN habitats h ON an.habitat_id = h.id
            LEFT JOIN conservation_statuses cs ON an.conservation_status_id = cs.id
            LEFT JOIN conservation_data cd ON an.id = cd.animal_id
            ORDER BY an.name ASC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "✅ Full query successful<br>";
            echo "📊 Sample result:<br>";
            echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "⚠️ Full query returned no results<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Full query failed: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>4. Next Steps:</h3>";
echo "<p>Nếu tất cả tables đều có dữ liệu, vấn đề có thể là:</p>";
echo "<ul>";
echo "<li>JOIN conditions không khớp</li>";
echo "<li>Foreign key relationships bị lỗi</li>";
echo "<li>Data types không tương thích</li>";
echo "</ul>";
?>
