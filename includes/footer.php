    </main>
    
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-2xl font-bold mb-2">
                        <i class="fas fa-graduation-cap mr-2"></i><?php echo SITE_NAME; ?>
                    </h3>
                    <p class="text-gray-400">Efficient student attendance management system</p>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-gray-400 mb-2">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                    <div class="flex space-x-4 justify-center md:justify-end">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-500 text-sm">
                <p>This system is for educational purposes only. For support, contact: support@school.edu</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript for interactive elements -->
    <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        // Add any JavaScript functionality here
        console.log('Attendance System Loaded');
        
        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-yellow-100');
            messages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    });
    </script>
</body>
</html>