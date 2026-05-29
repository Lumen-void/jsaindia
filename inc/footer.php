    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <div class="logo">
                    <a href="index.php" aria-label="Japneet S & Associates">
                        <img class="logo-img footer-logo-img" src="<?php echo h(jsa_asset_url('imagesandlogo/jsalogo2.jpeg')); ?>" alt="Japneet S & Associates logo">
                    </a>
                </div>
                <p class="muted">Chartered Accountants in Gurugram providing accounting, tax, payroll, secretarial compliance, and advisory services.</p>
                <div class="footer-contact">
                    <div>Email: <a href="mailto:jm@jsaindia.com">jm@jsaindia.com</a></div>
                    <div>Address: MGF Metropolis Mall, MG Road, Gurgaon</div>
                </div>
            </div>
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="industries.php">Industries</a></li>
                    <li><a href="case-studies.php">Case Studies</a></li>
                    <li><a href="updates.php">Regulatory Updates</a></li>
                    <li><a href="resources.php">Resources</a></li>
                    <li><a href="blog.php">Insights</a></li>
                </ul>
            </div>
            <div>
                <h4>Company</h4>
                <ul>
                    <li><a href="about.php">About</a></li>
                    <li><a href="team.php">Team</a></li>
                    <li><a href="careers.php">Careers</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Legal</h4>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">&copy; <?php echo date('Y'); ?> Japneet S & Associates. All rights reserved.</div>
        </div>
    </footer>
    <div class="booking-modal" id="bookingModal" aria-hidden="true">
        <div class="booking-overlay" data-booking-close></div>
        <div class="booking-dialog" role="dialog" aria-modal="true" aria-labelledby="bookingTitle">
            <div class="booking-top">
                <button class="booking-back" type="button" data-booking-close>Back</button>
                <button class="booking-close" type="button" data-booking-close aria-label="Close booking">Close</button>
            </div>
            <div class="booking-head">
                <h2 id="bookingTitle">Schedule your service</h2>
                <p>Check out our availability and book the date and time that works for you.</p>
            </div>
            <div class="booking-grid">
                <div class="booking-calendar">
                    <div class="booking-section-title">Select a Date and Time</div>
                    <div class="booking-month">
                        <button class="booking-nav" type="button" data-booking-prev aria-label="Previous month">&lsaquo;</button>
                        <div class="booking-month-label" id="bookingMonthLabel"></div>
                        <button class="booking-nav" type="button" data-booking-next aria-label="Next month">&rsaquo;</button>
                    </div>
                    <div class="booking-weekdays">
                        <span>Sun</span>
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                    </div>
                    <div class="booking-days" id="bookingDays"></div>
                </div>
                <div class="booking-times">
                    <div class="booking-section-title">India Standard Time (IST)</div>
                    <div class="booking-availability" id="bookingAvailability"></div>
                    <div class="booking-note" data-booking-note hidden></div>
                    <div class="booking-slots" data-booking-slots>
                        <button class="time-slot" type="button" data-time="11:00 am">11:00 am</button>
                        <button class="time-slot" type="button" data-time="11:30 am">11:30 am</button>
                        <button class="time-slot" type="button" data-time="12:00 pm">12:00 pm</button>
                        <button class="time-slot" type="button" data-time="12:30 pm">12:30 pm</button>
                        <button class="time-slot" type="button" data-time="2:00 pm">2:00 pm</button>
                        <button class="time-slot" type="button" data-time="2:30 pm">2:30 pm</button>
                        <button class="time-slot" type="button" data-time="3:00 pm">3:00 pm</button>
                        <button class="time-slot" type="button" data-time="3:30 pm">3:30 pm</button>
                        <button class="time-slot" type="button" data-time="4:00 pm">4:00 pm</button>
                    </div>
                </div>
                <div class="booking-details">
                    <div class="booking-section-title">Service Details</div>
                    <div class="booking-service" data-booking-service>Service</div>
                    <button class="booking-toggle" type="button" data-booking-toggle aria-expanded="false">More details</button>
                    <div class="booking-desc" data-booking-desc hidden></div>
                    <div class="booking-fields">
                        <input class="booking-input" id="bookingName" name="booking_name" type="text" placeholder="Full name" autocomplete="name" data-booking-name>
                        <input class="booking-input" id="bookingEmail" name="booking_email" type="email" placeholder="Work email" autocomplete="email" data-booking-email>
                        <input class="booking-input" id="bookingPhone" name="booking_phone" type="text" placeholder="Phone" autocomplete="tel" data-booking-phone>
                    </div>
                    <div class="booking-status" data-booking-status hidden></div>
                    <button class="booking-cta" type="button">Request to Book</button>
                </div>
            </div>
        </div>
    </div>
    <a class="mobile-sticky-cta" href="contact.php#inquiry-form" aria-label="Book a consultation">Book a Consultation</a>
    <div class="chatbot" id="jsa-chatbot">
        <button class="chatbot-fab" type="button" data-chat-toggle aria-haspopup="dialog" aria-expanded="false" aria-controls="jsa-chat-panel">
            <span class="chatbot-fab-dot" aria-hidden="true"></span>
            <span class="chatbot-fab-label">Ask JSA</span>
        </button>

        <section class="chatbot-panel" id="jsa-chat-panel" role="dialog" aria-modal="false" aria-label="JSA Assistant" aria-hidden="true">
            <header class="chatbot-head">
                <div>
                    <div class="chatbot-title">JSA Assistant</div>
                    <div class="chatbot-sub">Find pages, services, updates, and finance/tax/GST basics.</div>
                </div>
                <button class="chatbot-close" type="button" data-chat-close aria-label="Close chat">Close</button>
            </header>
            <div class="chatbot-chips" aria-label="Suggested questions">
                <button type="button" class="chip" data-chat-chip="Show me your services and pricing approach">Services</button>
                <button type="button" class="chip" data-chat-chip="Where can I find Regulatory Updates?">Updates</button>
                <button type="button" class="chip" data-chat-chip="I need help with GST compliance for a small business">GST</button>
                <button type="button" class="chip" data-chat-chip="How does Virtual CFO help and who is it for?">Virtual CFO</button>
            </div>
            <div class="chatbot-messages" data-chat-messages aria-live="polite" aria-relevant="additions"></div>
            <form class="chatbot-form" autocomplete="off">
                <textarea id="chatbotMessage" name="chat_message" rows="1" placeholder="Ask a question… (Press Enter to send, Shift+Enter for new line)" aria-label="Message" autocomplete="off"></textarea>
                <button class="btn" type="submit">Send</button>
            </form>
            <div class="chatbot-note">General info only; confirm with a qualified professional for your specific situation. Do not share sensitive details (PAN/Aadhaar, bank info).</div>
        </section>
    </div>
    <script src="<?php echo h(jsa_asset_url('assets/navbar.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/reveal.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/slider.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/form.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/updates.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/search.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/home.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/resources.js')); ?>" defer></script>
    <script src="<?php echo h(jsa_asset_url('assets/chatbot.js')); ?>" defer></script>
</body>
</html>
