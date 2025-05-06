<div class="room-card">
    <div class="room-image">
        <?php if (isset($room['image'])): ?>
            <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
        <?php else: ?>
            <img src="/api/placeholder/400/300" alt="Room Image">
        <?php endif; ?>
    </div>
    <div class="room-details">
        <h3><?php echo htmlspecialchars($room['name']); ?></h3>
        <div class="room-price">$<?php echo htmlspecialchars($room['price']); ?> / night</div>
        <div class="room-capacity">
            <i class="fas fa-users"></i> Up to <?php echo htmlspecialchars($room['capacity']); ?> people
        </div>
        <div class="room-description">
            <?php echo htmlspecialchars($room['description']); ?>
        </div>
        <div class="room-facilities">
            <i class="fas fa-concierge-bell"></i> Facilities: <?php echo htmlspecialchars($room['facilities']); ?>
        </div>
        <a href="views/kamar.php?id=<?php echo htmlspecialchars($room['id']); ?>" class="btn">View Details</a>
        <a href="views/reservasi.php?room_id=<?php echo htmlspecialchars($room['id']); ?>" class="btn btn-success">Book Now</a>
    </div>
</div>
