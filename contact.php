<?php
require_once __DIR__ . '/inc/data.php';
$pageTitle = 'Contact Japneet S & Associates | Gurugram CA Firm';
$metaDescription = 'Contact Japneet S & Associates in Gurugram for accounting, tax, payroll, compliance, and advisory support.';
$services = get_services();
$inquiryErrors = [];
$inquirySuccess = process_inquiry('Contact', $inquiryErrors);
$baseUrl = getenv('JSA_BASE_URL') ?: 'https://www.jsaindia.com';
$schemaExtra = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact', 'item' => $baseUrl . '/contact.php'],
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        '@id' => $baseUrl . '/contact.php#webpage',
        'name' => $pageTitle,
        'url' => $baseUrl . '/contact.php',
        'description' => $metaDescription,
        'isPartOf' => ['@id' => $baseUrl . '#website'],
        'about' => ['@id' => $baseUrl . '#organization'],
    ],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="pill reveal">Contact</div>
        <h1 class="reveal">Your financial clarity starts here.</h1>
        <p class="subhead reveal">Get in touch for support with taxation, business compliance, or advisory services—we're happy to guide you.</p>
    </div>
</section>

<section class="section" id="inquiry-form">
    <div class="container two-col">
        <div class="reveal" data-reveal="left">
            <h2>Send an inquiry</h2>
            <p class="muted">We respond quickly with the right desk and next steps.</p>
            <?php if ($inquirySuccess): ?>
                <div class="alert"><?php echo h($inquirySuccess); ?></div>
            <?php endif; ?>
            <?php if (!empty($inquiryErrors)): ?>
                <div class="alert error"><?php echo h(implode(' ', $inquiryErrors)); ?></div>
            <?php endif; ?>
            <form method="post" data-ajax="inquiry">
                <input type="hidden" name="form_type" value="inquiry">
                <input type="hidden" name="page_source" value="Contact">
                <input type="text" name="company" value="" autocomplete="off" tabindex="-1" aria-hidden="true" class="honeypot">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Name" autocomplete="name" required>
                    <input type="email" name="email" placeholder="Work email" autocomplete="email" required>
                </div>
                <div class="form-row">
                    <input type="text" name="phone" placeholder="Phone" autocomplete="tel" required>
                    <select name="selected_service" autocomplete="off">
                        <option value="">Select a service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo h($service['title']); ?>"><?php echo h($service['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <select name="preferred_slot" autocomplete="off">
                    <option value="">Preferred slot (optional)</option>
                    <option value="Weekday morning">Weekday morning</option>
                    <option value="Weekday afternoon">Weekday afternoon</option>
                    <option value="Weekday evening">Weekday evening</option>
                    <option value="Saturday">Saturday</option>
                </select>
                <textarea name="message" placeholder="How can we help?" autocomplete="off" required></textarea>
                <button type="submit" class="btn">Submit inquiry</button>
            </form>
        </div>
        <div class="reveal" data-reveal="right">
            <div class="card reveal" data-tilt>
                <h3>Office</h3>
                <p>MGF Metropolis Mall, MG Road, Gurgaon</p>
                <p class="muted">Mon - Fri | 10:00 am – 6:00 pm<br>Alternate Saturday's</p>
                <p class="muted">Email: <a href="mailto:jm@jsaindia.com">jm@jsaindia.com</a></p>
                <div class="map-embed" aria-label="MGF Metropolis Mall, MG Road, Gurgaon map">
                    <iframe
                        title="MGF Metropolis Mall, MG Road, Gurgaon"
                        src="https://www.google.com/maps?q=MGF%20Metropolis%20Mall%2C%20MG%20Road%2C%20Gurgaon&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
            <div class="card reveal" data-tilt style="margin-top:14px;">
                <h4>Prefer email?</h4>
                <a class="btn ghost" href="mailto:jm@jsaindia.com">jm@jsaindia.com</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-panel reveal" data-reveal="zoom">
            <div>
                <h3>Need a quick consult?</h3>
                <p class="muted">Share your questions and we will respond with clear next steps.</p>
            </div>
            <div>
                <a class="btn" href="services.php">Explore services</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
