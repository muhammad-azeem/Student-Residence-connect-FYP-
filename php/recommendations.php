<?php
// Database connection with error handling
try {
    $db = new mysqli('localhost', 'root', '', 'student_residence');

    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    // Set charset
    $db->set_charset("utf8mb4");

    // Check if it's a search request
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Get recommended hostels (with comments)
    $recommended_query = "
        SELECT h.*, AVG(c.sentiment_score) as avg_score
        FROM hostels h
        JOIN comments c ON h.id = c.hostel_id
        WHERE ? = '' OR h.name LIKE CONCAT('%', ?, '%')
        GROUP BY h.id
        ORDER BY avg_score DESC
    ";
    $stmt_recommended = $db->prepare($recommended_query);
    $stmt_recommended->bind_param("ss", $searchTerm, $searchTerm);
    $stmt_recommended->execute();
    $recommended_result = $stmt_recommended->get_result();

    if (!$recommended_result) {
        throw new Exception("Recommended hostels query failed: " . $db->error);
    }

    // Get new hostels (without comments)
    $new_query = "
        SELECT h.*
        FROM hostels h
        LEFT JOIN comments c ON h.id = c.hostel_id
        WHERE c.id IS NULL AND (? = '' OR h.name LIKE CONCAT('%', ?, '%'))
        ORDER BY h.id DESC
    ";
    $stmt_new = $db->prepare($new_query);
    $stmt_new->bind_param("ss", $searchTerm, $searchTerm);
    $stmt_new->execute();
    $new_result = $stmt_new->get_result();

    if (!$new_result) {
        throw new Exception("New hostels query failed: " . $db->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Recommendations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/main.css">
   <script src="../js/main.js"></script>
    <link rel="stylesheet" href="../css/rec.css">
    <style>
        .search-container {
    margin-top: 120px;
    display: flex;
    justify-content: center;
}
           .search-input {
    width: 99%;
    max-width: 600px;
    padding: 14px 22px;
    border: none;
    border-radius: 30px;
    font-size: 17px;
    
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: 0.3s ease;
}

.search-input:focus {
    background: #fff;
    border: 2px solid #4CAF50;
    outline: none;
    box-shadow: 0 0 12px rgba(76, 175, 80, 0.4);
}

        .search-results-info {
    text-align: center;   
    font-style: normal;
    font-size: 18px;
    color: #333;    
    padding: 10px 20px;
    border-radius: 10px;
    display: inline-block;
    
}
@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .search-results-info {
        margin-top: 15px;
    }
}

    </style>
</head>
<body>
    <header class="header">
        <nav class="nav container">
            <a href="../index.html" class="logo" style="display: block; width: 150px;">
                <img src="../img/logo.png" alt="Student Residence Connect Logo" style="width: 100%; height: auto;">
            </a>
            <div class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li><a href="../index.html" class="nav-link">Home</a></li>
                    <li><a href="../hostels.html" class="nav-link">Hostels</a></li>
                    <li><a href="../about.html" class="nav-link">About Us</a></li>
                    <li><a href="../contact.html" class="nav-link">Contact</a></li>
                </ul>
            </div>
            <div class="nav-toggle" id="nav-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Search Bar -->
        <div class="search-container">
            <form id="searchForm" method="GET">
                <input type="text" 
                       class="search-input" 
                       id="searchInput" 
                       name="search" 
                       placeholder="Search hostels by name..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>"
                       autocomplete="off">
                <button type="submit" style="display: none;">Search</button>
            </form>
            <?php if (!empty($searchTerm)): ?>
                <div class="search-results-info">
                      Showing results for: "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                    <a href="?" style="margin-left: 15px; color: #4CAF50;">Clear</a>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="section-title1">Recommended Hostels</h1>
        <div class="hostel-grid">
            <?php if ($recommended_result->num_rows > 0): ?>
                <?php while ($hostel = $recommended_result->fetch_assoc()): ?>
                    <a href="http://localhost/project/hostel-details.html?id=<?php echo $hostel['id']; ?>" class="hostel-card-link">
                        <div class="hostel-card">
                            <?php if (!empty($hostel['images'])): ?>
                                <img src="<?php echo htmlspecialchars($hostel['images']); ?>"
                                     alt="<?php echo htmlspecialchars($hostel['name']); ?>"
                                     class="hostel-image"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/300x200?text=Image+Not+Available';">
                            <?php else: ?>
                                <div class="hostel-image" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                    <span>No Image Available</span>
                                </div>
                            <?php endif; ?>
                            <div class="hostel-info">
                                <h3 class="hostel-name"><?php echo htmlspecialchars($hostel['name'] ?? 'No Name'); ?></h3>
                                <?php if (isset($hostel['address'])): ?>
                                    <p class="hostel-meta">
                                        <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i>
                                        <?php echo htmlspecialchars($hostel['address']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($hostel['description'])): ?>
                                    <p class="hostel-description">
                                        <i class="fas fa-align-left" style="margin-right: 5px; color: #555;"></i>
                                        <?php echo htmlspecialchars($hostel['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php
                                $score = $hostel['avg_score'] ?? 0;
                                $sentiment_class = 'neutral';
                                if ($score > 0.1) $sentiment_class = 'positive';
                                elseif ($score < -0.1) $sentiment_class = 'negative';

                                $recommendation_text = ($score > 0.1) ? "Recommended" : (($score < -0.1) ? "Not Recommended" : "Neutral");
                                ?>
                                <span class="sentiment-score <?php echo $sentiment_class; ?>">
                                    <?php echo $recommendation_text; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <?php if (!empty($searchTerm)): ?>
                        No recommended hostels found matching your search.
                    <?php else: ?>
                        No hostels with comments yet.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="section-title2">New Hostels</h1>
        <div class="hostel-grid">
            <?php if ($new_result->num_rows > 0): ?>
                <?php while ($hostel = $new_result->fetch_assoc()): ?>
                    <a href="http://localhost/project/hostel-details.html?id=<?php echo $hostel['id']; ?>" class="hostel-card-link">
                        <div class="hostel-card">
                            <?php if (!empty($hostel['images'])): ?>
                                <img src="<?php echo htmlspecialchars($hostel['images']); ?>"
                                     alt="<?php echo htmlspecialchars($hostel['name']); ?>"
                                     class="hostel-image"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/300x200?text=Image+Not+Available';">
                            <?php else: ?>
                                <div class="hostel-image" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                                    <span>No Image Available</span>
                                </div>
                            <?php endif; ?>
                            <div class="hostel-info">
                                <h3 class="hostel-name"><?php echo htmlspecialchars($hostel['name'] ?? 'No Name'); ?></h3>
                                <?php if (isset($hostel['location'])): ?>
                                    <p class="hostel-meta"><?php echo htmlspecialchars($hostel['location']); ?></p>
                                <?php endif; ?>
                                <?php if (isset($hostel['description'])): ?>
                                    <p class="hostel-description">
                                        <i class="fas fa-align-left" style="margin-right: 5px; color: #555;"></i>
                                        <?php echo htmlspecialchars($hostel['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <span class="sentiment-score neutral">New Listing</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <?php if (!empty($searchTerm)): ?>
                        No new hostels found matching your search.
                    <?php else: ?>
                        No new hostels currently.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <h3>Student Residence Connect</h3>
                    <p>Finding your perfect student accommodation made easy.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.html">Home</a></li>
                        <li><a href="../about.html">About Us</a></li>
                        <li><a href="../contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Info</h4>
                    <ul>
                        <li>University of Sahiwal</li>
                        <li>Sahiwal, Pakistan</li>
                        <li>Email: mazeemtvid@gmail.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <span id="current-year"></span> Student Residence Connect. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php $db->close(); ?>
    
    <script>
 
        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            
           
            
            // For browsers that don't support 'input' event well
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    searchForm.submit();
                }
            });
        });
        
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

    </script>
</body>
</html>