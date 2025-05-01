<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
?>

<?php
require_once __DIR__ . '/header.php';
$lang = LanguageController::getInstance();
?>

<main class="container">
    <h1><?php echo $lang->translate('why_us_title'); ?></h1>

    <div class="grid">
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_secure_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_secure_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_easy_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_easy_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_free_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_free_text'); ?></p>
        </article>
    </div>

    <section class="key-features">
        <h2><?php echo $lang->translate('why_us_features_title'); ?></h2>
        <div class="features-container">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Instant QR Generation</h3>
                    <p><?php echo $lang->translate('why_us_feature_1'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>European Bank Support</h3>
                    <p><?php echo $lang->translate('why_us_feature_2'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Multilingual</h3>
                    <p><?php echo $lang->translate('why_us_feature_3'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Cross-Device Compatible</h3>
                    <p><?php echo $lang->translate('why_us_feature_4'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <style>
        .key-features {
            margin: 2rem 0;
        }
        .features-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .feature-item:hover {
            background-color: var(--secondary-hover);
        }
        .feature-icon {
            color: var(--primary);
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .feature-content {
            flex: 1;
        }
        .feature-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }
        .feature-content p {
            margin: 0;
        }
        
        /* Desktop grid layout */
        @media (min-width: 769px) {
            .features-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
        }
    </style>

    <section>
        <h2><?php echo $lang->translate('why_us_use_cases_title'); ?></h2>
        <div class="grid">
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_1_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_1_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_2_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_2_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_3_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_3_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_4_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_4_text'); ?></p>
            </article>
        </div>
    </section>

    <section class="comparison-section">
        <h2><?php echo $lang->translate('why_us_comparison_title'); ?></h2>
        
        <div class="comparison-table-container">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th><?php echo $lang->translate('why_us_comparison_feature'); ?></th>
                        <th class="highlight"><?php echo $lang->translate('why_us_comparison_qr_transfer'); ?></th>
                        <th><?php echo $lang->translate('why_us_comparison_banking_apps'); ?></th>
                        <th><?php echo $lang->translate('why_us_comparison_payment_apps'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $lang->translate('why_us_comparison_price'); ?></td>
                        <td class="highlight"><?php echo $lang->translate('why_us_comparison_price_qr'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_price_banking'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_price_payment'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang->translate('why_us_comparison_apps'); ?></td>
                        <td class="highlight"><?php echo $lang->translate('why_us_comparison_apps_qr'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_apps_banking'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_apps_payment'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang->translate('why_us_comparison_account'); ?></td>
                        <td class="highlight"><?php echo $lang->translate('why_us_comparison_account_qr'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_account_banking'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_account_payment'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang->translate('why_us_comparison_notification'); ?></td>
                        <td class="highlight"><?php echo $lang->translate('why_us_comparison_notification_qr'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_notification_banking'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_notification_payment'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang->translate('why_us_comparison_static'); ?></td>
                        <td class="highlight"><?php echo $lang->translate('why_us_comparison_static_qr'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_static_banking'); ?></td>
                        <td><?php echo $lang->translate('why_us_comparison_static_payment'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .comparison-section {
            margin: 3rem 0;
        }
        
        .comparison-table-container {
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-color);
            font-size: 0.95rem;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid var(--border-color);
        }
        
        .comparison-table th {
            background-color: var(--secondary-bg);
            font-weight: 600;
        }
        
        .comparison-table .highlight {
            background-color: rgba(var(--primary-rgb), 0.1);
            font-weight: 500;
        }
        
        @media (prefers-color-scheme: dark) {
            .comparison-table th {
                background-color: var(--secondary-bg-dark);
            }
            
            .comparison-table .highlight {
                background-color: rgba(var(--primary-rgb), 0.15);
            }
        }
        
        @media (max-width: 768px) {
            .comparison-table {
                font-size: 0.85rem;
            }
            
            .comparison-table th,
            .comparison-table td {
                padding: 0.5rem;
            }
        }
    </style>
</main>

<!-- Structured data for search engines -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "<?php echo $lang->translate('why_us_secure_title'); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php echo $lang->translate('why_us_secure_text'); ?>"
      }
    },
    {
      "@type": "Question",
      "name": "<?php echo $lang->translate('why_us_easy_title'); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php echo $lang->translate('why_us_easy_text'); ?>"
      }
    },
    {
      "@type": "Question",
      "name": "<?php echo $lang->translate('why_us_free_title'); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php echo $lang->translate('why_us_free_text'); ?>"
      }
    }
  ]
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
