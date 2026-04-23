<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // ============================================================
        // general
        // ============================================================
        $this->migrator->inGroup('general', function ($blueprint): void {
            $blueprint->add('site_name', 'Криптон');
            $blueprint->add('tagline', 'Индустриальная экология и Битрикс24');
            $blueprint->add('meta_description', 'ООО «Криптон» — полный цикл управления отходами III–IV класса и внедрение цифровых продуктов на базе Битрикс24 для промышленного и государственного сектора. Лицензия Росприроднадзора № 077 № 00428.');
            $blueprint->add('brand_wordmark', 'Криптон');
            $blueprint->add('brand_wordmark_accent_char', 'о');
            $blueprint->add('brand_mark_letter', 'К');
            $blueprint->add('brand_subtitle', 'Exologix · Est. 2014');
        });

        // ============================================================
        // strip (top dark strip)
        // ============================================================
        $this->migrator->inGroup('strip', function ($blueprint): void {
            $blueprint->add('status_text', 'online · пн–пт 09:00 — 19:00 msk');
            $blueprint->add('location_text', 'москва · россия');
            $blueprint->add('license_text', 'лицензия рпн № 077 № 00428');
            $blueprint->add('lang_label', 'рус / en');
            $blueprint->add('telegram_url', '#');
            $blueprint->add('whatsapp_url', '#');
        });

        // ============================================================
        // nav
        // ============================================================
        $this->migrator->inGroup('nav', function ($blueprint): void {
            $blueprint->add('phone_number', '+7 910 542 10 10');
            $blueprint->add('phone_label', 'горячая линия');
            $blueprint->add('primary_cta_label', 'Запросить КП');
        });

        // ============================================================
        // hero
        // ============================================================
        $this->migrator->inGroup('hero', function ($blueprint): void {
            $blueprint->add('ref_code_html', '<b>REF / 2026-Q2 · ED. 014</b><br>документ № RF-ST · гл. / ru<br>→ обновлено 14 апр. 2026');
            $blueprint->add('hazard_label', 'III–IV');
            $blueprint->add('headline_html', 'Экология<br>как <em>инженерная</em><br>дисциплина.');
            $blueprint->add('lede_html', '<b>ООО «Криптон»</b> — полный цикл управления отходами III–IV класса и внедрение цифровых продуктов на основе Битрикс24 для промышленного и государственного сектора. Без бумажной волокиты. С фиксированной ставкой. Под лицензией Росприроднадзора.');

            $blueprint->add('cta_primary_label', 'Запросить коммерческое предложение');
            $blueprint->add('cta_primary_anchor', '#contact');
            $blueprint->add('cta_secondary_label', 'Каталог услуг');
            $blueprint->add('cta_secondary_anchor', '#services');

            $blueprint->add('signature_name', 'А.&thinsp;Соколов');
            $blueprint->add('signature_caption_html', 'генеральный директор<br>ООО «Криптон» · подпись № 014');

            // Card A — light, "10+ лет" stat
            $blueprint->add('card_a_kicker', '§ 001 · на рынке с 2014 г.');
            $blueprint->add('card_a_big_value', '10');
            $blueprint->add('card_a_big_suffix', '+');
            $blueprint->add('card_a_label_strong', 'лет');
            $blueprint->add('card_a_label_text', 'накопленного опыта работы с отходами III–IV класса опасности');
            $blueprint->add('card_a_stat1_value', '500+');
            $blueprint->add('card_a_stat1_label', 'клиентов');
            $blueprint->add('card_a_stat2_value', '40+');
            $blueprint->add('card_a_stat2_label', 'специалистов');
            $blueprint->add('card_a_stat3_value', '10+');
            $blueprint->add('card_a_stat3_label', 'регионов');

            // Card B — dark, license
            $blueprint->add('card_b_kicker', '§ 002 · сертификация');
            $blueprint->add('card_b_title_html', 'Лицензия<br><em>Росприроднадзора</em>');
            $blueprint->add('card_b_license_number', '№ 077');
            $blueprint->add('card_b_license_detail', '№ 00428 от 12.04.2019 г.');
            $blueprint->add('card_b_class_label', 'Класс');
            $blueprint->add('card_b_class_value', 'III — IV — V опасности');
        });

        // ============================================================
        // about (§ 01)
        // ============================================================
        $this->migrator->inGroup('about', function ($blueprint): void {
            $blueprint->add('section_index', '§ 01');
            $blueprint->add('section_kicker', 'о компании');
            $blueprint->add('section_heading_html', 'Инженерный подход к <em>управлению отходами</em> и цифровая дисциплина.');
            $blueprint->add('legal_block_html', '<b>ООО «Криптон»</b>ИНН 7728123456<br>ОГРН 1147746...<br>КПП 772801001');

            $blueprint->add('body_heading_html', 'Проводим <em>полный цикл</em> — от накопления до утилизации отходов III–IV класса опасности.');
            $blueprint->add('body_paragraph', 'ООО «Криптон» более 10 лет оказывает профессиональный сервис в области управления отходами. Помимо полевой работы — сбор, транспортирование, обезвреживание — мы внедряем цифровые продукты на базе Битрикс24, превращая экологический учёт из бумажного кошмара в управляемый процесс.');

            $blueprint->add('cta_label', 'Досье компании');
            $blueprint->add('cta_url', '#');
        });

        // ============================================================
        // metrics (dark band)
        // ============================================================
        $this->migrator->inGroup('metrics', function ($blueprint): void {
            $blueprint->add('header_html', '<b>Показатели работы</b> · 2024 → 2025 · внутренний аудит');
            $blueprint->add('stamp_text', 'ref / Ω-2025-014');
        });

        // ============================================================
        // services_section (§ 02)
        // ============================================================
        $this->migrator->inGroup('services_section', function ($blueprint): void {
            $blueprint->add('section_index', '§ 02');
            $blueprint->add('section_kicker', 'каталог услуг');
            $blueprint->add('section_heading_html', 'Три направления, <em>один</em> оператор.');
            $blueprint->add('section_note_html', '<b>3 строки</b>15 подуслуг<br>фиксированные ставки<br>договор за 48 часов');
        });

        // ============================================================
        // bitrix (§ 03)
        // ============================================================
        $this->migrator->inGroup('bitrix', function ($blueprint): void {
            $blueprint->add('kicker', '§ 03 · цифровой слой');
            $blueprint->add('heading_html', 'Битрикс24 для <em>экологов</em> — превращает журнал учёта в панель управления.');
            $blueprint->add('paragraph', 'Мы строим на платформе Битрикс24 решения, которые закрывают специфические задачи отходообразующего бизнеса: автоматизация паспортов отходов, учёт лицензий контрагентов, диспетчеризация вывозов, интеграция с 1С и электронной отчётностью.');

            $blueprint->add('cta_label', 'Запросить демо-стенд');
            $blueprint->add('cta_url', '#contact');

            $blueprint->add('mock_url', 'b24.rf-st.ru / экология / канбан · q2 · 2026');
            $blueprint->add('mock_version', 'v 24.14.1');
            $blueprint->add('mock_footer_left', 'связано · 1С + ЭДО');
            $blueprint->add('mock_footer_right_html', '<b>↻ live</b> · 14:32 msk');
            $blueprint->add('caption', 'fig.02 — интерфейс корпоративного портала · демо-проект');
        });

        // ============================================================
        // industries_section (§ 04)
        // ============================================================
        $this->migrator->inGroup('industries_section', function ($blueprint): void {
            $blueprint->add('section_index', '§ 04');
            $blueprint->add('section_kicker', 'отрасли');
            $blueprint->add('section_heading_html', 'Работаем с <em>отходообразователями</em> любого профиля.');
            $blueprint->add('section_note_html', '<b>6+ сегментов</b>реестр клиентов<br>открыт по NDA<br>кейсы — по запросу');
        });

        // ============================================================
        // waste_section (§ 05)
        // ============================================================
        $this->migrator->inGroup('waste_section', function ($blueprint): void {
            $blueprint->add('section_index', '§ 05');
            $blueprint->add('section_kicker', 'реестр отходов');
            $blueprint->add('section_heading_html', 'С какими <em>отходами</em> работаем ежедневно.');
            $blueprint->add('section_note_html', '<b>III–IV класс</b>опасности<br>полный перечень — в ФККО<br>код 9 81 000 00 00 0 и др.');
        });

        // ============================================================
        // process_section (§ 06)
        // ============================================================
        $this->migrator->inGroup('process_section', function ($blueprint): void {
            $blueprint->add('section_index', '§ 06');
            $blueprint->add('section_kicker', 'процесс');
            $blueprint->add('section_heading_html', 'От заявки до акта — <em>5 шагов</em> без сюрпризов.');
            $blueprint->add('section_note_html', '<b>ср. срок · 7 дн.</b>от первого звонка<br>до закрывающих<br>документов');
        });

        // ============================================================
        // plans_section (§ 07)
        // ============================================================
        $this->migrator->inGroup('plans_section', function ($blueprint): void {
            $blueprint->add('section_index', '§ 07');
            $blueprint->add('section_kicker', 'битрикс24 · тарифы');
            $blueprint->add('section_heading_html', 'Три пакета <em>внедрения</em> — от MVP до предприятия.');
            $blueprint->add('section_note_html', '<b>гарантия</b>30 дней на доработки<br>поддержка 3 / 6 / 12 мес.<br>обучение — в комплекте');
        });

        // ============================================================
        // coverage (§ 08)
        // ============================================================
        $this->migrator->inGroup('coverage', function ($blueprint): void {
            $blueprint->add('kicker', '§ 08 · география');
            $blueprint->add('heading_html', 'Работаем <em>по РФ</em> — от Москвы до Зауралья.');
            $blueprint->add('paragraph', 'Собственный автопарк и партнёрская сеть лицензированных операторов в 10+ регионах. Выезд инженера — в течение 72 часов в любую точку покрытия.');
            $blueprint->add('map_meta_html', 'fig. 03 — <b>HQ: MSK 55.68°N · 37.54°E</b>');
        });

        // ============================================================
        // quote (testimonial)
        // ============================================================
        $this->migrator->inGroup('quote', function ($blueprint): void {
            $blueprint->add('reviewer_name', 'Е. Новикова');
            $blueprint->add('reviewer_role', 'директор по устойч. развитию');
            $blueprint->add('reviewer_ref', 'отзыв · 2025 · ref / Q-014');
            $blueprint->add('quote_html', 'Передали весь цикл управления отходами и CRM на Криптон. За полгода — <em>минус&nbsp;28%</em> расходов и ноль штрафов от надзора.');
            $blueprint->add('company_name', 'ПАО «Металлкомплект»');
            $blueprint->add('company_description', "металлообработка\nпроизводственный\nкомплекс\n4 филиала");
        });

        // ============================================================
        // cta_band (signal orange band)
        // ============================================================
        $this->migrator->inGroup('cta_band', function ($blueprint): void {
            $blueprint->add('heading_html', 'Нужен <em>оператор</em>, который закроет отходы и цифру в одном договоре?');
            $blueprint->add('paragraph', 'Оставьте заявку — в течение рабочего дня подготовим коммерческое предложение и пришлём на почту.');
            $blueprint->add('cta_primary_label', 'Запросить КП');
            $blueprint->add('cta_primary_url', '#contact');
            $blueprint->add('cta_secondary_label', 'Скачать пресс-кит');
            $blueprint->add('cta_secondary_url', '#');
        });

        // ============================================================
        // contact
        // ============================================================
        $this->migrator->inGroup('contact', function ($blueprint): void {
            $blueprint->add('heading_html', 'Поговорим <em>по существу</em>.');

            $blueprint->add('address', 'Москва, ул. Миклухо-Маклая, 34');
            $blueprint->add('phone', '+7 910 542 10 10');
            $blueprint->add('email', 'info@rf-st.ru');
            $blueprint->add('hours', 'Пн–Пт · 09:00 — 19:00');
            $blueprint->add('messengers', 'Telegram · WhatsApp');

            $blueprint->add('form_label_name', '§ 01 · Имя и компания');
            $blueprint->add('form_label_phone', '§ 02 · Телефон');
            $blueprint->add('form_label_email', '§ 03 · E-mail');
            $blueprint->add('form_label_message', '§ 04 · Что интересует');

            $blueprint->add('form_placeholder_name', 'Иван Петров · ООО «Ромашка»');
            $blueprint->add('form_placeholder_phone', '+7 999 000 00 00');
            $blueprint->add('form_placeholder_email', 'name@company.ru');
            $blueprint->add('form_placeholder_message', 'Коротко опишите задачу...');

            $blueprint->add('form_submit_label', 'Отправить заявку');
            $blueprint->add('form_consent_text', 'отправляя, вы соглашаетесь на обработку персональных данных в соответствии с 152-ФЗ');
            $blueprint->add('personal_data_consent_text', 'Я даю согласие на обработку моих персональных данных (имя, телефон, e-mail, сообщение) ООО «Криптон» в целях ответа на заявку и предоставления коммерческого предложения в соответствии с Федеральным законом № 152-ФЗ «О персональных данных».');
        });

        // ============================================================
        // footer
        // ============================================================
        $this->migrator->inGroup('footer', function ($blueprint): void {
            $blueprint->add('about_paragraph', 'ООО «Криптон» — полный цикл управления отходами III–IV класса и цифровая трансформация на базе Битрикс24 для промышленного сектора.');
            $blueprint->add('copyright_html', '© 2014 — 2026 · ООО «Криптон» · ИНН 7728123456 · ОГРН 1147746... · лицензия № 077 № 00428');

            $blueprint->add('legal_link_policy_label', 'Политика');
            $blueprint->add('legal_link_policy_url', '#');
            $blueprint->add('legal_link_oferta_label', 'Оферта');
            $blueprint->add('legal_link_oferta_url', '#');
            $blueprint->add('legal_link_152fz_label', '152-ФЗ');
            $blueprint->add('legal_link_152fz_url', '#');

            $blueprint->add('massive_wordmark', 'Криптон');
            $blueprint->add('massive_italic_char', 'о');
        });

        // ============================================================
        // legal
        // ============================================================
        $this->migrator->inGroup('legal', function ($blueprint): void {
            $blueprint->add('legal_name', 'ООО «Криптон»');
            $blueprint->add('inn', '7728123456');
            $blueprint->add('kpp', '772801001');
            $blueprint->add('ogrn', '1147746...');
            $blueprint->add('license_number', '№ 077 № 00428');
            $blueprint->add('license_issuer', 'Росприроднадзор РФ');
            $blueprint->add('license_date', '12.04.2019 г.');
        });

        // ============================================================
        // integrations
        // ============================================================
        $this->migrator->inGroup('integrations', function ($blueprint): void {
            $blueprint->add('turnstile_site_key', null);
            $blueprint->add('turnstile_secret_key', null);
            $blueprint->add('notify_email', null);
            $blueprint->add('yandex_metrika_id', null);
        });

        // ============================================================
        // design (the tweaks panel, now server-side)
        // ============================================================
        $this->migrator->inGroup('design', function ($blueprint): void {
            $blueprint->add('signal', 'hazard');
            $blueprint->add('paper', 'bone');
            $blueprint->add('head_weight', 'serif');
        });
    }
};
