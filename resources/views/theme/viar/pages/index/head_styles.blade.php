    <!-- CSS -->

    <style>
        @font-face {
            font-family: Trajan;
            font-display: swap;
            src: url("/fonts/Trajan-Pro-3.woff2") format("woff2"), url("/fonts/Trajan-Pro-3.woff") format("woff");
            font-weight: 400;
            font-style: normal
        }

        @font-face {
            font-family: Trajan;
            font-display: swap;
            src: url("/fonts/Trajan-Pro-3-SemiBold.woff2") format("woff2"), url("/fonts/Trajan-Pro-3-SemiBold.woff") format("woff");
            font-weight: 600;
            font-style: normal
        }

        @font-face {
            font-family: icon-font;
            src: url("/fonts/icon-font.eot?zapzd9");
            src: url("/fonts/icon-font.eot?zapzd9#iefix") format("embedded-opentype"), url("/fonts/icon-font.ttf?zapzd9") format("truetype"), url("/fonts/icon-font.woff?zapzd9") format("woff"), url("/fonts/icon-font.svg?zapzd9#icon-font") format("svg");
            font-weight: 400;
            font-style: normal;
            font-display: swap
        }

        .footer-social h3 {
            font-weight: 500;
            font-size: 16px;
            line-height: 22px;
            margin-bottom: 10px
        }

        .footer-list li {
            border-radius: 5px;
            margin-left: -12px;
            position: relative;
            margin-bottom: 15px
        }

        .footer-list li::before {
            content: "";
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%), #c4c4c4;
            opacity: 0;
            position: absolute;
            left: 0;
            top: 0
        }

        .footer-list li:last-child {
            margin-bottom: 0
        }

        .footer-list a {
            display: inline-block;
            padding: 5px 12px;
            position: relative;
            z-index: 2
        }

        .portraits-heading h1 {
            max-width: 500px;
            font: 400 60px/70px Trajan, serif
        }

        .portraits-heading h1 span {
            color: #fa7846
        }

        .ellipse_top {
            margin-bottom: -70px
        }

        .kviz-input__title:empty {
            height: 21px
        }

        @media screen and (max-width:93.75em) {
            .section-frame {
                max-width: 1200px;
                padding: 0 20px
            }

            .services-photo a {
                width: 250px
            }

            .header-bar {
                max-width: 1200px
            }

            .header-social {
                display: none
            }

            .top-item__title {
                font-size: 16px
            }

            .why-form__photo {
                width: 250px;
                left: -32px
            }

            .kviz-item {
                padding: 40px;
                padding-top: 0
            }

            .kviz-stock {
                width: 350px
            }

            .stock-photo {
                height: 215px
            }

            .stock-list {
                display: flex;
                flex-wrap: wrap
            }

            .stock-item {
                width: 48%;
                padding: 15px;
                box-sizing: border-box
            }

            .stock-item img {
                max-width: 100px
            }

            .stock-item:last-child {
                margin: 0 auto
            }

            .kviz-finsh-photo {
                width: 460px
            }

            .stock-gift {
                left: 55px
            }

            .kviz-arrow {
                left: 3px;
                top: 30px
            }

            .footer-list ul {
                font-size: 16px
            }

            .services-item__title {
                min-height: 60px
            }

            .stock-gift {
                display: none
            }
        }

        @media screen and (max-width:84.375em) {
            .services-item__title {
                min-height: auto
            }

            .kviz-arrow {
                display: none
            }

            .services-photo img {
                height: 380px
            }

            .ellipse_top {
                margin-bottom: -27px
            }

            .menu-list {
                width: 620px
            }
        }

        @media screen and (max-width:61.25em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 80px), 1fr))
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .section-frame {
                max-width: 740px
            }

            .file-save__title span {
                font-size: 11px
            }

            .ellipse {
                margin-top: -68px
            }

            .faq-info h2 {
                display: none
            }

            .faq-photo {
                display: none
            }

            .why-form__photo {
                display: none
            }

            .why-item br {
                display: none
            }

            .footer-list ul {
                font-size: 14px
            }

            .work-arrow {
                display: none !important
            }

            .step-counter {
                display: none
            }

            .kviz-tabs i {
                display: none
            }

            .kviz-item {
                flex-direction: column
            }

            .stock-full {
                display: none
            }

            .kviz-content {
                margin: 0 auto
            }

            .kviz-stock {
                margin: 0 auto
            }

            .kviz-button {
                justify-content: center
            }

            .kviz-group__title {
                margin-top: 20px
            }

            .kviz-finsh-photo {
                width: 360px
            }

            .submit-gift__arrow {
                display: none !important
            }

            .services-photo img {
                height: 485px
            }

            .header-item_pc {
                display: none
            }

            .header-button {
                margin-left: auto
            }

            .header-bar {
                padding: 20px 0
            }

            .header-cart i {
                width: 40px;
                height: 40px
            }

            .header-user {
                width: 40px;
                height: 40px
            }

            .burger {
                display: flex
            }

            .stock-full_tablet {
                display: block
            }

            .stock-list__title {
                display: block;
                text-align: center;
                font-size: 14px;
                line-height: 22px;
                margin-top: 10px
            }

            .stock-list {
                margin-top: 3px
            }

            .kviz-step {
                margin-bottom: 15px
            }

            .thanks {
                width: 700px
            }
        }

        @media screen and (max-width:43.75em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 30px), 1fr))
            }

            .services-photo a {
                height: 52px;
                font-size: 15px
            }

            .portraits-btn a {
                height: 52px;
                font-size: 15px
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 80px), 1fr))
            }

            .kviz-next {
                height: 52px;
                font-size: 15px
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 30px), 1fr))
            }

            .registration-sub {
                height: 52px;
                font-size: 15px
            }

            .kviz-sub {
                height: 52px;
                font-size: 15px
            }

            .kviz-thanks__info a {
                height: 52px;
                font-size: 15px
            }

            .top-btn {
                height: 52px;
                font-size: 15px
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .popup-photo__submit {
                height: 52px;
                font-size: 15px
            }

            .log-sub {
                height: 52px;
                font-size: 15px
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .scroll-button {
                height: 52px;
                font-size: 15px
            }

            .section-frame {
                max-width: 375px;
                padding: 0 20px
            }

            .popup-frame {
                padding: 30px 15px;
                box-sizing: border-box
            }

            .popup-photo {
                width: 100%;
                padding: 60px 35px
            }

            .page-title {
                font-size: 24px;
                line-height: 29px
            }

            .photo-mokap {
                display: none
            }

            /* .kviz-input_pc {
                display: none
            } */

            .box-list {
                flex-wrap: wrap
            }

            .box-list .kviz-radio {
                margin-bottom: 9px
            }

            .kviz-input_mob {
                display: block;
                margin-top: 13px
            }

            .select-page {
                height: 52px;
                padding: 0 20px
            }

            .logo img {
                width: 130px
            }

            .kviz-politics {
                margin-top: 15px
            }

            .portraits-heading h1 {
                text-align: center;
                font-size: 36px;
                line-height: 43px
            }

            .portraits-heading p {
                text-align: center;
                margin-top: 15px;
                font-size: 16px;
                line-height: 22px
            }

            .portraits-title {
                text-align: center;
                font-size: 36px;
                line-height: 43px
            }

            .popup-photo__submit {
                margin-top: 20px
            }

            .portraits-slide {
                padding-top: 95px
            }

            .portraits-btn {
                flex-direction: column-reverse
            }

            .portraits-btn a {
                width: 100%;
                margin: 0;
                margin-bottom: 13px
            }

            .portraits-btn a:first-child {
                margin-bottom: 0
            }

            .portraits-gift {
                width: 100%;
                padding: 0;
                display: flex;
                align-items: flex-start;
                margin-top: 30px;
                background: 0 0
            }

            .portraits-gift img {
                display: none
            }

            .portraits-gift .portraits-gift__arrow {
                display: block;
                top: -20px;
                right: 35px;
                transform: rotate(50deg)
            }

            .portraits-gift p {
                max-width: 100%;
                padding-right: 40px;
                text-align: left;
                margin-top: 0
            }

            .gift-photo_mob {
                width: 31px;
                height: 33px;
                display: block !important;
                margin-right: 13px
            }

            .ellipse-arrow {
                width: 24px;
                height: 24px;
                font-size: 6px
            }

            .ellipse-arrow_white {
                border: 5px solid #fff
            }

            .ellipse {
                padding-top: 17px
            }

            .ellipse {
                margin-top: -25px
            }

            .page-title {
                font-size: 31px;
                line-height: 37px
            }

            .services-photo img {
                height: 225px
            }

            .services-item__title {
                font-size: 16px;
                line-height: 20px;
                min-height: 40px
            }

            .services-info p {
                font-size: 15px;
                line-height: 18px
            }

            .services-info__title {
                font-size: 16px;
                line-height: 19px
            }

            .services-list img {
                max-width: 100%;
                display: block
            }

            .kviz-content {
                width: 100%
            }

            .top-title p br {
                display: none
            }

            .kviz-input__title:empty {
                display: none
            }

            .file-save__title p {
                font-size: 15px
            }

            .file-save {
                background: rgba(255, 255, 255, .5)
            }

            .file-save__item {
                max-height: 52px
            }

            .page-input__item input {
                height: 52px;
                font-size: 15px
            }

            .certificate-frame .ellipse_top:first-child {
                display: none
            }

            .faq-header h3 {
                font-size: 16px;
                line-height: 24px
            }

            .faq-body {
                font-size: 15px;
                line-height: 22px
            }

            .footer-social_pc {
                display: none
            }

            .footer-list ul {
                display: none;
                padding-top: 15px;
                margin-left: 56px
            }

            .footer-list li {
                margin-bottom: 5px
            }

            .footer-list a {
                width: fit-content
            }

            .kviz-item {
                padding: 0 20px 30px
            }

            .kviz-group__title {
                font-size: 16px;
                line-height: 24px;
                margin-bottom: 12px
            }

            .kviz-radio span {
                font-size: 15px;
                line-height: 22px
            }

            .kviz-stock {
                width: 100%
            }

            .stock-item {
                height: 90px;
                font-size: 11px;
                line-height: 15px
            }

            .stock-item img {
                max-height: 70px
            }

            .stock-item:last-child img {
                max-height: 60px
            }

            .kviz-button {
                margin-top: 20px;
                flex-direction: column
            }

            .kviz-skip {
                margin-left: 0;
                margin-top: 12px;
                font-size: 15px
            }

            .kviz-finis-group {
                width: 100%;
                margin-top: 0
            }

            .kviz-sub {
                width: 100%;
                margin: 0 auto
            }

            .submit-gift {
                margin-top: 20px
            }

            .kviz-finsh-photo {
                display: none
            }

            .kviz-thanks {
                margin: 130px 0 90px
            }

            .kviz-thanks__title {
                margin-top: 0
            }

            .kviz-thanks__title .h3_old {
                font-size: 24px;
                line-height: 29px
            }

            .kviz-thanks__title p {
                font-size: 16px;
                line-height: 22px
            }

            .kviz-thanks__icon {
                display: none
            }

            .kviz-thanks__info p {
                font-size: 13px
            }

            .kviz-thanks__info a {
                width: 100%
            }

            .burge-menu__item {
                font-size: 15px
            }

            .burge-menu__item h3 i {
                font-size: 8px
            }

            .burge-menu__item a {
                opacity: .75
            }

            .kviz-radio-descriptor img {
                display: block
            }

            .language ul {
                position: static;
                padding: 0;
                padding-top: 10px
            }

            .popup-login {
                width: 100%;
                padding: 35px 20px
            }

            .popup-registration {
                width: 100%;
                padding: 60px 20px
            }

            .registration-group {
                margin: 20px 0
            }

            .scroll-button {
                height: 40px;
                right: 20px;
                bottom: 20px
            }
        }

        .kviz-tabs i {
            color: #fa7846;
            margin-right: 15px;
            font-size: 12px
        }

        .stock-gift span {
            font-weight: 600;
            color: #fa7846
        }

        .work-arrow {
            width: 112px;
            height: 22px;
            position: absolute;
            top: 60px;
            right: -120px
        }

        .why-form__photo {
            position: absolute;
            bottom: 0;
            left: 0
        }

        .faq-info h2 {
            text-align: left
        }

        .faq-photo {
            position: absolute;
            top: -90px;
            left: 0
        }

        @media screen and (max-width:93.75em) {
            .section-frame {
                max-width: 1200px;
                padding: 0 20px
            }

            .services-photo a {
                width: 250px
            }

            .header-bar {
                max-width: 1200px
            }

            .header-social {
                display: none
            }

            .top-item__title {
                font-size: 16px
            }

            .why-form__photo {
                width: 250px;
                left: -32px
            }

            .kviz-item {
                padding: 40px;
                padding-top: 0
            }

            .kviz-stock {
                width: 350px
            }

            .stock-photo {
                height: 215px
            }

            .stock-list {
                display: flex;
                flex-wrap: wrap
            }

            .stock-item {
                width: 48%;
                padding: 15px;
                box-sizing: border-box
            }

            .stock-item img {
                max-width: 100px
            }

            .stock-item:last-child {
                margin: 0 auto
            }

            .kviz-finsh-photo {
                width: 460px
            }

            .stock-gift {
                left: 55px
            }

            .kviz-arrow {
                left: 3px;
                top: 30px
            }

            .services-item__title {
                min-height: 60px
            }

            .stock-gift {
                display: none
            }
        }

        @media screen and (max-width:61.25em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 80px), 1fr))
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .section-frame {
                max-width: 740px
            }

            .file-save__title span {
                font-size: 11px
            }

            .ellipse {
                margin-top: -68px
            }

            .faq-info h2 {
                display: none
            }

            .faq-photo {
                display: none
            }

            .why-form__photo {
                display: none
            }

            .why-item br {
                display: none
            }

            .work-arrow {
                display: none !important
            }

            .step-counter {
                display: none
            }

            .kviz-tabs i {
                display: none
            }

            .kviz-item {
                flex-direction: column
            }

            .stock-full {
                display: none
            }

            .kviz-content {
                margin: 0 auto
            }

            .kviz-stock {
                margin: 0 auto
            }

            .kviz-button {
                justify-content: center
            }

            .kviz-group__title {
                margin-top: 20px
            }

            .kviz-finsh-photo {
                width: 360px
            }

            .submit-gift__arrow {
                display: none !important
            }

            .services-photo img {
                height: 485px
            }

            .header-item_pc {
                display: none
            }

            .header-button {
                margin-left: auto
            }

            .header-bar {
                padding: 20px 0
            }

            .header-cart i {
                width: 40px;
                height: 40px
            }

            .header-user {
                width: 40px;
                height: 40px
            }

            .burger {
                display: flex
            }

            .stock-full_tablet {
                display: block
            }

            .stock-list__title {
                display: block;
                text-align: center;
                font-size: 14px;
                line-height: 22px;
                margin-top: 10px
            }

            .stock-list {
                margin-top: 3px
            }

            .kviz-step {
                margin-bottom: 15px
            }

            .thanks {
                width: 700px
            }
        }

        [class^=fa-] {
            font-family: icon-font !important;
            speak: never;
            font-style: normal;
            font-weight: 400;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        .fa-arrow-down:before {
            content: "\e901"
        }

        .fa-arrow-next:before {
            content: "\e902"
        }

        .fa-arrow-prev:before {
            content: "\e903"
        }

        .fa-facebook:before {
            content: "\e904"
        }

        .fa-close:before {
            content: "\e905"
        }

        .fa-instagram:before {
            content: "\e906"
        }

        .fa-cart:before {
            content: "\e907"
        }

        .fa-user:before {
            content: "\e908"
        }

        html {
            line-height: 1.15;
            -webkit-text-size-adjust: 100%
        }

        body {
            margin: 0
        }

        main {
            display: block
        }

        h1 {
            font-size: 2em;
            margin: .67em 0
        }

        a {
            background-color: transparent
        }

        b {
            font-weight: bolder
        }

        code {
            font-family: monospace, monospace;
            font-size: 1em
        }

        img {
            border-style: none
        }

        input,
        select {
            font-family: inherit;
            font-size: 100%;
            line-height: 1.15;
            margin: 0
        }

        input {
            overflow: visible
        }

        select {
            text-transform: none
        }

        [type=submit] {
            -webkit-appearance: button
        }

        [type=submit]::-moz-focus-inner {
            border-style: none;
            padding: 0
        }

        [type=submit]:-moz-focusring {
            outline: ButtonText dotted 1px
        }

        [type=checkbox],
        [type=radio] {
            box-sizing: border-box;
            padding: 0
        }

        ::-webkit-file-upload-button {
            -webkit-appearance: button;
            font: inherit
        }

        body {
            min-width: 320px;
            color: #1e2533;
            font: 18px/1.5 Inter, sans-serif
        }

        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
            font-size: inherit;
            line-height: inherit;
            font-weight: inherit
        }

        ul {
            padding: 0;
            margin: 0;
            list-style: none
        }

        a {
            text-decoration: none;
            outline: 0;
            color: #1e2533
        }

        form {
            box-sizing: border-box
        }

        input {
            outline: 0;
            box-sizing: border-box
        }

        option,
        select {
            outline: 0;
            appearance: none
        }

        select::-ms-expand {
            display: none
        }

        label {
            margin-bottom: 0
        }

        .section-frame {
            max-width: 1350px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 3px
        }

        ::-webkit-scrollbar-track {
            background-color: #e9e9e9
        }

        ::-webkit-scrollbar-track-piece {
            background-color: #e9e9e9
        }

        ::-webkit-scrollbar-thumb {
            background-color: #fa7846
        }

        ::-webkit-scrollbar-corner {
            background-color: #fa7846
        }

        ::-webkit-resizer {
            background-color: #666
        }

        .popup-frame {
            width: 100%;
            height: 100vh;
            display: none;
            justify-content: center;
            align-items: flex-start;
            position: fixed;
            background: rgba(8, 7, 35, .5);
            backdrop-filter: blur(20px);
            overflow: auto;
            left: 0;
            top: 0;
            z-index: 80
        }

        .header {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: 20
        }

        .header-bar {
            padding: 20px 0;
            display: flex;
            align-items: center;
            box-sizing: border-box
        }

        .header-social {
            display: flex;
            align-items: center;
            margin-left: 55px
        }

        .header-social li {
            margin-right: 6px
        }

        .header-social li:last-child {
            margin-right: 0
        }

        .header-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ad8780;
            box-shadow: 0 2px 0 #91665e;
            color: #fff;
            font-size: 15px;
            border-radius: 50%
        }

        .header-menu {
            margin: 0 auto
        }

        .header-menu ul {
            display: flex;
            align-items: center;
            font-size: 16px
        }

        .header-menu li {
            margin-right: 30px
        }

        .header-menu li:last-child {
            margin-right: 0
        }

        .header-menu a {
            display: flex;
            align-items: center
        }

        .header-menu i {
            margin-left: 7px;
            font-size: 6px
        }

        .header-button {
            display: flex;
            align-items: center
        }

        .header-button a {
            flex-shrink: 0;
            margin-right: 10px
        }

        .header-button a:last-child {
            margin-right: 0
        }

        .header-user {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            border-radius: 50%;
            color: #fa7846;
            border: .833333px solid #fbf2ea
        }

        .header-cart {
            display: flex;
            align-items: center
        }

        .header-cart i {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fa7846;
            border-radius: 50%;
            color: #fff;
            position: relative
        }

        .header-cart i::after {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1e2533;
            border: 1px solid #fff;
            box-sizing: border-box;
            position: absolute;
            right: 2px;
            top: 5px
        }

        .language {
            margin-left: 16px;
            position: relative
        }

        .language p {
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            font-size: 15px;
            opacity: .8;
            padding: 10px;
            min-width: 50px
        }

        .language i {
            margin-left: 5px;
            font-size: 6px
        }

        .language ul {
            width: 100%;
            padding: 10px;
            display: none;
            box-sizing: border-box;
            border-radius: 0 0 8px 8px;
            background-color: #fff;
            position: absolute;
            left: 0;
            top: 90%;
            font-size: 15px;
            line-height: 18px
        }

        .language li {
            margin-bottom: 10px
        }

        .language li:last-child {
            margin-bottom: 0
        }

        .language a {
            color: rgba(35, 41, 55, .75);
            display: flex
        }

        .language img {
            width: 16px;
            height: 14px;
            object-fit: contain;
            margin-right: 4px
        }

        .burger {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background-color: #fff;
            border-radius: 50%;
            margin-left: 9px;
            border: .833333px solid #fbf2ea;
            box-sizing: border-box
        }

        .burger span {
            width: 18px;
            height: 1.5px;
            display: block;
            background-color: #1e2533;
            margin-bottom: 4px
        }

        .burger span:last-child {
            margin-bottom: 0
        }

        .logo img {
            width: 175px;
            display: block
        }

        .menu {
            width: 1005px;
            display: none;
            background-color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .03);
            position: absolute;
            margin-right: 210px;
            overflow: hidden;
            z-index: 46;
            right: 0;
            top: 108px
        }

        .menu-frame {
            display: none;
            border-top: 1px solid #fbf2ea
        }

        .menu-frame .section-frame {
            height: 100%
        }

        .menu-frame[data-menu="1"] {
            display: grid
        }

        .menu-frame__content {
            width: fit-content;
            display: flex;
            margin-left: auto
        }

        .menu-link {
            width: 305px;
            max-height: 630px;
            overflow: auto;
            padding-top: 60px;
            border-right: 1px solid #fbf2ea;
            margin-right: 30px;
            box-sizing: border-box;
            flex-shrink: 0
        }

        .menu-link li {
            margin-bottom: 20px
        }

        .menu-link li:last-child {
            margin-bottom: 0
        }

        .menu-link a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px 20px;
            color: #000;
            border-bottom: 2px solid transparent
        }

        .menu-link a span {
            opacity: .75
        }

        .menu-link a i {
            color: #fa7846;
            margin-left: 10px;
            transform: scale(0)
        }

        .menu-list {
            width: 670px;
            padding-bottom: 140px;
            padding-top: 60px;
            padding-right: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 30px), 1fr));
            grid-gap: 30px 30px;
            box-sizing: border-box;
            display: none
        }

        .menu-list img {
            max-width: 100%;
            display: block
        }

        .menu-list img {
            border-radius: 8px
        }

        .menu-list[data-link="1"] {
            display: grid
        }

        .burge-menu {
            width: 100%;
            height: 100vh;
            display: none;
            padding-bottom: 50px;
            overflow: auto;
            background-color: #fff;
            position: fixed;
            z-index: 32;
            left: 0;
            top: 0;
            box-sizing: border-box
        }

        .header-bar_burger {
            padding: 20px !important;
            position: static;
            border-bottom: 1px solid #fbf2ea
        }

        .burger-close {
            font-size: 14px
        }

        .burge-menu__list {
            margin-top: 15px;
            padding: 0 20px
        }

        .burge-menu__item {
            margin-bottom: 10px
        }

        .burge-menu__item:last-child {
            margin-bottom: 0
        }

        .burge-menu__item h3 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #fbf2ea;
            padding-bottom: 10px
        }

        .burge-menu__item h3 i {
            font-size: 10px
        }

        .burge-menu__item ul {
            display: none;
            padding: 23px 0;
            padding-left: 20px;
            border-bottom: 1px solid #fbf2ea
        }

        .burge-menu__item ul li {
            margin-bottom: 15px
        }

        .burge-menu__item ul a {
            position: relative;
            overflow: hidden
        }

        .burge-menu__item ul a::after {
            content: "";
            width: 0%;
            height: 1px;
            background-color: #1e2533;
            border-radius: inherit;
            position: absolute;
            transform-origin: left;
            bottom: 0;
            right: 0;
            left: auto
        }

        .language_burger {
            width: calc(100% - 40px);
            box-sizing: border-box;
            border: 1px solid #fbf2ea;
            border-radius: 8px;
            padding: 9px 20px;
            margin: 20px 20px 0
        }

        .language_burger p {
            justify-content: space-between
        }

        .footer-social {
            margin-top: 40px
        }

        .footer-social ul {
            display: flex;
            align-items: center
        }

        .footer-social li {
            margin-right: 8px
        }

        .footer-social li:last-child {
            margin-right: 0
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            border-radius: 50%;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%), #ad8780;
            box-shadow: 0 2.77338px 0 #e87145;
            font-size: 16px
        }

        .footer-list h3 span {
            display: none
        }

        .footer-social_mob {
            display: none
        }

        .page-title {
            text-align: center;
            font: 400 45px/1.4 Trajan, serif;
            letter-spacing: -.03em
        }

        .services-list img {
            max-width: 100%;
            display: block
        }

        .services-item__title {
            display: block;
            font: 600 20px/1.5 Trajan, serif;
            text-align: center;
            letter-spacing: -.02em;
            margin-bottom: 15px
        }

        .services-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative
        }

        .services-photo img {
            width: 100%;
            height: 450px;
            display: block;
            object-fit: cover;
            border-radius: 8px
        }

        .services-photo::before {
            content: "";
            width: 100%;
            height: 100%;
            position: absolute;
            background: rgba(30, 37, 51, .75);
            border-radius: 8px;
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0
        }

        .services-photo a {
            max-width: 100%;
            width: 278px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            position: absolute;
            transform: scale(0)
        }

        .services-photo a i {
            font-size: 8px;
            margin-left: 9px
        }

        .services-info {
            margin-top: 20px;
            text-align: center
        }

        .services-info p {
            margin-top: 5px;
            font-size: 16px;
            color: rgba(30, 37, 51, .75)
        }

        .services-info__title {
            font-weight: 600;
            display: block
        }

        .services-item_hide {
            display: none
        }

        .portraits {
            position: relative
        }

        .portraits-bg {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: 65% bottom;
            position: absolute;
            bottom: 0;
            right: 0
        }

        /* .portraits-slider {
            height: 100vh
        } */

        .portraits-slide {
            padding: 170px 0 100px;
            box-sizing: border-box
        }

        .portraits-slide .section-frame {
            position: relative
        }

        .portraits-info {
            position: relative;
            z-index: 2
        }

        .portraits-heading h1,
        .portraits-heading h2 {
            max-width: 500px;
            font: 400 60px/70px Trajan, serif
        }

        .portraits-heading h1 span,
        .portraits-heading h2 span {
            color: #fa7846
        }

        .portraits-heading p {
            max-width: 370px;
            margin-top: 20px;
            opacity: .8
        }

        .portraits-title {
            max-width: 500px;
            font: 400 60px/70px Trajan, serif
        }

        .portraits-title span {
            color: #fa7846
        }

        .portraits-btn {
            margin-top: 30px;
            display: flex;
            align-items: center
        }

        .portraits-btn a {
            max-width: 100%;
            width: 290px;
            height: 65px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin-right: 10px
        }

        .portraits-btn a:last-child {
            margin-right: 0
        }

        .portraits-btn .portraits-btn__style {
            background: #ad8780;
            box-shadow: 0 3px 0 #91665e
        }

        .portraits-gift {
            width: 315px;
            margin-top: 70px;
            background: rgba(255, 255, 255, .75);
            border-radius: 8px;
            position: relative;
            padding-bottom: 20px;
            padding-top: 5px
        }

        .portraits-gift p {
            max-width: 195px;
            font-size: 15px;
            line-height: 20px;
            text-align: center;
            margin: 10px auto 0
        }

        .portraits-gift b {
            font-weight: 600
        }

        .gift-photo {
            display: block;
            margin: -27px auto 0
        }

        .portraits-gift__arrow {
            position: absolute;
            width: 30px;
            height: 90px;
            right: 22px;
            top: -30px
        }

        .portraits-arrow {
            width: 100%;
            display: flex;
            align-items: center;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3
        }

        .portraits-arrow a {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            box-shadow: 0 2px 0 rgba(156, 165, 181, .25);
            color: #fa7846;
            border-radius: 50%;
            position: absolute;
            font-size: 15px
        }

        .portraits-prev {
            left: -72px
        }

        .portraits-next {
            right: -72px
        }

        .portraits-dots {
            position: absolute;
            z-index: 30;
            right: 0;
            top: -100px
        }

        .ellipse {
            padding-top: 25px;
            margin-top: -87px;
            overflow: hidden;
            position: relative;
            z-index: 3
        }

        .ellipse img {
            width: calc(100% + 100px);
            display: block;
            margin-left: -50px
        }

        .ellipse-arrow {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto;
            font-size: 7px;
            position: absolute;
            left: 0;
            right: 0;
            top: 0
        }

        .ellipse-arrow_white {
            color: #fff;
            background: #fa7846;
            border: 7px solid #fff
        }

        .kviz-item {
            padding: 70px;
            display: none;
            align-items: flex-start;
            justify-content: space-between;
            position: relative
        }

        .kviz-content {
            width: 540px
        }

        .kviz-stock {
            width: 580px
        }

        .kviz-step {
            display: flex;
            align-items: center
        }

        .step-counter {
            position: relative;
            padding: 14px 16px;
            background: #fff;
            border-radius: 8px;
            font-size: 14px;
            line-height: 17px;
            color: rgba(30, 37, 51, .5)
        }

        .step-counter::before {
            content: "";
            width: calc(100% - 8px);
            height: calc(100% - 8px);
            border: 1px solid #fbf2ea;
            box-sizing: border-box;
            border-radius: inherit;
            position: absolute;
            top: 4px;
            left: 4px
        }

        .kviz-group__title {
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: -.02em
        }

        .kviz-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 80px), 1fr));
            grid-gap: 13px 80px;
            box-sizing: border-box
        }

        .kviz-group img {
            max-width: 100%;
            display: block
        }

        .kviz-group_tab .kviz-radio {
            margin-bottom: 0
        }

        .kviz-radio {
            display: flex;
            align-items: flex-start;
            margin-bottom: 13px;
            position: relative
        }

        .kviz-radio:last-child {
            margin-bottom: 0
        }

        .kviz-radio span {
            display: block;
            opacity: .75;
            font-size: 16px
        }

        .kviz-radio label {
            display: block
        }

        .kviz-radio input {
            display: none
        }

        .check {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            border-radius: 50%;
            flex-shrink: 0;
            margin-right: 9px
        }

        .check::before {
            content: "";
            width: 14px;
            height: 14px;
            display: block;
            border-radius: inherit;
            background-color: #fa7846;
            transform: scale(0)
        }

        .kviz-radio_active .check::before {
            transform: scale(1)
        }

        .kviz-button {
            display: flex;
            align-items: center;
            margin-top: 35px
        }

        .kviz-next {
            max-width: 100%;
            width: 275px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative
        }

        .kviz-next i {
            font-size: 8px;
            margin-left: 9px
        }

        .tab-group {
            display: none
        }

        .stock-photo {
            width: 100%;
            height: 370px;
            object-fit: cover;
            border-radius: 8px
        }

        .stock-photo[data-stock="1"] {
            display: block
        }

        .stock-full {
            position: relative
        }

        .stock-gift {
            width: 212px;
            height: 212px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background: url("images/bg/gift-bg.svg") center no-repeat;
            background-size: 212px;
            position: absolute;
            top: 80px;
            left: 20px
        }

        .stock-gift svg {
            width: 31px;
            height: 33px;
            display: block
        }

        .stock-gift p {
            margin-top: 10px;
            max-width: 153px;
            font-size: 16px;
            text-align: center
        }

        .kviz-arrow {
            width: 171px;
            height: 61px;
            position: absolute;
            left: -96px;
            top: 30px
        }

        .stock-list {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 3) - 11px), 1fr));
            grid-gap: 11px 11px;
            box-sizing: border-box
        }

        .stock-list img {
            max-width: 100%;
            display: block
        }

        .stock-item {
            padding: 20px;
            height: 120px;
            box-sizing: border-box;
            background-color: #fff;
            border-radius: 8px;
            position: relative;
            font-size: 15px;
            line-height: 20px;
            overflow: hidden
        }

        .stock-item::before {
            content: "";
            width: calc(100% - 10px);
            height: calc(100% - 10px);
            border: 1px solid #fbf2ea;
            box-sizing: border-box;
            border-radius: inherit;
            position: absolute;
            top: 5px;
            left: 5px
        }

        .stock-item img {
            position: absolute;
            display: block;
            right: 0;
            bottom: 0
        }

        .stock-item p {
            max-width: 110px
        }

        .stock-item b {
            font-weight: 600
        }

        .kviz-skip {
            margin-left: 30px;
            color: #fa7846;
            font-weight: 600;
            font-size: 16px;
            position: relative
        }

        .kviz-skip::before {
            content: "";
            width: 100%;
            height: 1px;
            background-color: #fa7846;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0 auto
        }

        .kviz-radio-descriptor {
            width: 275px;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(30, 37, 51, .15);
            border-radius: 8px;
            position: absolute;
            background-color: #fff;
            font-size: 14px;
            line-height: 22px;
            transform: scale(0);
            left: 0;
            top: 35px;
            z-index: 3;
            transform-origin: top center
        }

        .kviz-radio-descriptor img {
            width: 100%;
            display: none;
            margin-bottom: 10px;
            border-radius: 8px
        }

        .kviz-radio-descriptor::before {
            content: "";
            width: fit-content;
            border: 7px solid transparent;
            border-bottom: 7px solid #fff;
            position: absolute;
            top: -13px;
            left: 105px
        }

        .radio-info {
            width: 17px;
            height: 17px;
            margin-left: 8px;
            display: block;
            margin-top: 3px
        }

        .kviz-input__icon {
            width: 21px;
            height: 19px
        }

        .stock-gift_finish p {
            font-weight: 400;
            max-width: 167px
        }

        .stock-full_empty {
            height: 370px
        }

        .country-list {
            width: 100%;
            height: 210px;
            display: none;
            overflow-y: scroll;
            background-color: #fff;
            color: #000;
            position: absolute;
            z-index: 20;
            top: 55px
        }

        .country-list .country-item img {
            margin-right: 15px
        }

        .country-item {
            height: 35px;
            display: flex;
            align-items: center;
            padding-left: 10px
        }

        .country-item img {
            width: 18px;
            object-fit: cover;
            position: static !important
        }

        .country-item p {
            margin: 0;
            color: #000
        }

        .country-item span {
            width: 65px;
            height: 100%;
            padding-left: 5px;
            display: flex;
            align-items: center;
            font-size: 16px;
            background-color: #eef7ff;
            margin-left: auto;
            box-sizing: border-box
        }

        .country-item-active {
            position: absolute;
            left: 25px;
            padding-left: 0
        }

        .country-item-active::after {
            content: "\e901";
            font-family: icon-font !important;
            display: block;
            color: #1e2533;
            font-size: 6px;
            margin-left: 7px
        }

        .input-counter {
            padding-left: 70px !important
        }

        .kviz-finis-group {
            width: 580px;
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 30px), 1fr));
            grid-gap: 20px 30px;
            box-sizing: border-box
        }

        .kviz-finis-group img {
            max-width: 100%;
            display: block
        }

        .kviz-input__title {
            font-size: 14px;
            display: block;
            margin-bottom: 5px
        }

        .kviz-input__title span {
            color: #fa7846
        }

        .registration-sub {
            max-width: 100%;
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative
        }

        .registration-sub input {
            display: none
        }

        .registration-politics {
            margin-top: 0 !important
        }

        .page-input__item {
            display: flex;
            align-items: center;
            position: relative
        }

        .page-input__item input {
            width: 100%;
            height: 60px;
            padding-left: 25px;
            padding-right: 45px;
            background: #fff;
            border: 1px solid #fff;
            border: 1px solid #fbf2ea;
            font-size: 16px;
            color: #9ca5b5;
            box-sizing: border-box;
            border-radius: 8px
        }

        .page-input__item svg {
            position: absolute;
            right: 25px
        }

        .file-save {
            display: block;
            background: #fff;
            border: 1.5px dashed #fa7846;
            box-sizing: border-box;
            border-radius: 8px;
            position: relative
        }

        .file-save input {
            width: 100%;
            height: 100%;
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0
        }

        .kviz-input_mob {
            display: none
        }

        .file-save__item {
            width: 100%;
            min-height: 60px;
            display: flex;
            align-items: center;
            padding: 10px;
            justify-content: center;
            box-sizing: border-box
        }

        .file-save__item svg {
            width: 24px;
            height: 18px;
            margin-right: 13px;
            display: block
        }

        .file-save__title p {
            max-width: 180px;
            overflow: hidden;
            font-size: 16px;
            line-height: 1.2;
            flex-shrink: 0
        }

        .file-save__title span {
            display: block;
            font-size: 11px;
            letter-spacing: .02em;
            color: #9ca5b5;
            margin-top: 3px
        }

        .js-file-upload {
            display: none
        }

        .kviz-messege {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 11px
        }

        .kviz-messege__tab {
            width: 133px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(173, 135, 128, .5);
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 14px
        }

        .kviz-messege__tab svg {
            width: 20px;
            height: 15px;
            display: block;
            margin-right: 7px
        }

        .kviz-messege__tab input {
            position: absolute;
            opacity: 0;
            transform: scale(0)
        }

        .kviz-messege__tab_wh {
            background: #1cc33d
        }

        .kviz-sub {
            max-width: 100%;
            width: 275px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin-right: 30px
        }

        .kviz-sub input {
            display: none
        }

        .submit-gift {
            display: flex;
            align-items: center;
            position: relative
        }

        .submit-gift svg {
            width: 31px;
            height: 32px;
            display: block;
            margin-right: 10px
        }

        .submit-gift p {
            font-weight: 600;
            font-size: 16px
        }

        svg {
            flex-shrink: 0
        }

        .submit-gift__arrow {
            width: 115px !important;
            height: 73px !important;
            margin: 0;
            position: absolute;
            right: -90px;
            top: 35px
        }

        .kviz-politics {
            display: flex;
            align-items: center;
            margin-top: 40px
        }

        .kviz-politics svg {
            width: 14px;
            height: 16px;
            display: block;
            margin-right: 10px
        }

        .kviz-politics p {
            max-width: 325px;
            font-size: 13px;
            opacity: .75
        }

        .kviz-politics a {
            color: #fa7846
        }

        .kviz-finsh-photo {
            position: absolute;
            right: 0;
            bottom: 0
        }

        .kviz-thanks {
            max-width: 410px;
            margin: 110px auto;
            text-align: center
        }

        .kviz-thanks__icon {
            display: block;
            margin: 0 auto
        }

        .kviz-thanks__title {
            margin: 40px 0 20px
        }

        .kviz-thanks__title .h3_old {
            font: 400 35px Trajan, serif;
            letter-spacing: -.03em
        }

        .kviz-thanks__title p {
            margin-top: 10px
        }

        .kviz-thanks__info p {
            font-size: 14px;
            max-width: 240px;
            margin: 0 auto;
            opacity: .75
        }

        .kviz-thanks__info a {
            max-width: 100%;
            width: 258px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin: 10px auto 0;
            background: #1cc33d;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(28, 195, 61, .25), 0 2.77338px 0 #1a9e34
        }

        .kviz-thanks__info a svg {
            width: 20px;
            height: 15px;
            margin-right: 10px
        }

        .top-list img {
            max-width: 100%;
            display: block
        }

        .top-item__title {
            text-align: center;
            display: block;
            letter-spacing: -.02em;
            font: 600 20px Trajan, serif;
            margin: 20px auto 15px
        }

        .top-item_hide {
            display: none
        }

        .top-photo {
            width: 100%;
            display: block;
            border-radius: 5px;
            overflow: hidden
        }

        .top-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .top-btn {
            max-width: 100%;
            width: 275px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin: 0 auto
        }

        .file-title_green {
            color: #1cc33d
        }

        .js-file-multiple {
            display: none
        }

        .js-file-multiple svg {
            width: 24px;
            height: 24px;
            display: block;
            margin-right: 10px
        }

        .why-form__group img {
            max-width: 100%;
            display: block
        }

        .why-sub input {
            display: none
        }

        .faq-item {
            padding: 18px 0;
            border-bottom: 1px solid #fbf2ea
        }

        .faq-header {
            display: flex;
            align-items: flex-start
        }

        .faq-header h3 {
            font-weight: 600;
            line-height: 26px;
            letter-spacing: -.02em;
            margin-top: 6px
        }

        .faq-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
            position: relative;
            margin-right: 20px
        }

        .faq-icon::after,
        .faq-icon::before {
            content: "";
            background-color: #fa7846;
            position: absolute
        }

        .faq-icon::before {
            width: 14px;
            height: 1px
        }

        .faq-icon::after {
            width: 1px;
            height: 14px
        }

        .faq-body {
            /* display: none; */
            padding-top: 8px;
            padding-left: 56px;
            opacity: .75;
            font-size: 16px
        }

        .faq-item_hide {
            display: none
        }

        .faq-title_tablet {
            display: none
        }

        .gift-photo_mob {
            display: none !important
        }

        .stock-full_tablet {
            display: none
        }

        .stock-list__title {
            display: none
        }

        .popup-frame {
            padding: 50px 0;
            box-sizing: border-box
        }

        .popup-photo {
            width: 890px;
            padding: 70px 80px 195px;
            box-sizing: border-box;
            margin: auto;
            position: relative;
            background-color: #fbf2ea;
            border-radius: 10px
        }

        .popup-photo::before {
            content: "";
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            border: 2px solid #fff;
            box-sizing: border-box;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px
        }

        .popup-close {
            color: #1e2533;
            position: absolute;
            top: 33px;
            right: 33px
        }

        .popup-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 20px), 1fr));
            grid-gap: 13px 20px;
            box-sizing: border-box;
            margin-top: 30px
        }

        .popup-group img {
            max-width: 100%;
            display: block
        }

        .popup-photo-title {
            font-size: 30px
        }

        .popup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 20px), 1fr));
            grid-gap: 13px 20px;
            box-sizing: border-box;
            margin-top: 20px
        }

        .select-page {
            width: 100%;
            height: 60px;
            background: url("images/icon/angle-down.svg") center right 25px no-repeat, #fff;
            font-size: 16px;
            border: 1px solid #fbf2ea;
            box-sizing: border-box;
            border-radius: 8px;
            padding: 0 25px
        }

        .file-add {
            display: flex;
            align-items: center;
            color: #fa7846;
            font-size: 14px;
            margin-top: 5px;
            margin-left: 5px
        }

        .file-add span {
            position: relative
        }

        .file-add span::before {
            content: "";
            width: 100%;
            height: 1px;
            background-color: #fa7846;
            position: absolute;
            left: 0;
            right: 0;
            margin: 0 auto;
            bottom: 0
        }

        .file-add svg {
            width: 16px;
            height: 17px;
            margin-right: 10px
        }

        .box {
            margin-top: 20px
        }

        .box h3 {
            font-size: 14px
        }

        .box-list {
            display: flex;
            align-items: center;
            margin-top: 8px
        }

        .box-list .kviz-radio {
            margin-right: 20px;
            margin-bottom: 0
        }

        .box-list .kviz-radio:last-child {
            margin-right: 0
        }

        .popup-photo__submit {
            max-width: 100%;
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin-top: 30px
        }

        .popup-photo__submit input {
            display: none
        }

        .photo-mokap {
            right: -25px;
            display: block;
            position: absolute;
            bottom: 0
        }

        .popup-grid__file .file-save {
            margin-bottom: 10px
        }

        .popup-grid__file .file-save:last-child {
            margin-bottom: 0
        }

        .file-save_hide {
            display: none
        }

        .popup-grid__select .kviz-input {
            margin-bottom: 20px
        }

        .popup-grid__select .kviz-input:last-child {
            margin-bottom: 0
        }

        .js-popup {
            display: none
        }

        .popup-login {
            width: 430px;
            padding: 70px;
            margin: 0 auto;
            background: #fbf2ea;
            border-radius: 10px;
            margin: auto;
            position: relative
        }

        .popup-login::before {
            content: "";
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            border: 2px solid #fff;
            box-sizing: border-box;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px
        }

        .popup-login .popup-close {
            color: #9ca5b5
        }

        .popup-registration {
            width: 890px;
            padding: 70px;
            margin: 0 auto;
            background: #fbf2ea;
            border-radius: 10px;
            margin: auto;
            position: relative
        }

        .popup-registration::before {
            content: "";
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            border: 2px solid #fff;
            box-sizing: border-box;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px
        }

        .popup-registration .popup-close {
            color: #9ca5b5
        }

        .popup-log-group {
            margin-top: 30px
        }

        .popup-log-group .kviz-input {
            margin-bottom: 20px
        }

        .popup-log-save {
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .popup-log-save a {
            font-size: 14px;
            color: #fa7846;
            position: relative
        }

        .popup-log-save a::before {
            content: "";
            width: 100%;
            height: 1px;
            background-color: #fa7846;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0 auto
        }

        .popup-log-check {
            display: flex;
            align-items: center
        }

        .popup-log-check p {
            font-size: 14px;
            color: #9ca5b5
        }

        .popup-log-check input {
            display: none
        }

        .log-check {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fa7846;
            border-radius: 5px;
            flex-shrink: 0;
            margin-right: 9px
        }

        .log-check svg {
            width: 12px;
            height: 9px;
            display: block;
            transform: scale(0)
        }

        .log-check_active svg {
            transform: scale(1)
        }

        .log-sub {
            max-width: 100%;
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            margin-top: 12px
        }

        .log-sub input {
            display: none
        }

        .log-creat {
            margin-top: 20px;
            text-align: center;
            font-size: 14px
        }

        .log-creat a {
            color: #fa7846;
            position: relative
        }

        .log-creat a::before {
            content: "";
            width: 100%;
            height: 1px;
            background-color: #fa7846;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0 auto
        }

        .log-social {
            margin-top: 20px
        }

        .log-social h3 {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #9ca5b5
        }

        .log-social h3 span {
            margin: 0 14px
        }

        .log-social h3::after,
        .log-social h3::before {
            content: "";
            width: 100%;
            height: 1px;
            background: rgba(156, 165, 181, .25);
            display: block
        }

        .log-social p {
            text-align: center;
            font-size: 16px;
            margin: 10px 0
        }

        .log-social li {
            margin-right: 6px
        }

        .log-social li:last-child {
            margin-right: 0
        }

        .log-social ul {
            display: flex;
            align-items: center;
            justify-content: center
        }

        .log-social ul a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
            justify-content: center;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%), #ad8780;
            box-shadow: 0 2.77338px 0 #e87145;
            border-radius: 50%;
            color: #fff;
            font-size: 16px
        }

        .registration-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc((100% / 2) - 20px), 1fr));
            grid-gap: 15px 20px;
            box-sizing: border-box;
            margin: 30px 0
        }

        .registration-group img {
            max-width: 100%;
            display: block
        }

        .thanks {
            width: 900px;
            display: none;
            background: #fbf2ea;
            margin: auto;
            position: relative;
            padding: 100px 30px;
            border-radius: 10px
        }

        .thanks::before {
            content: "";
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            border: 2px solid #fff;
            box-sizing: border-box;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px
        }

        .thanks .kviz-thanks {
            margin: 0 auto
        }

        .scroll-button {
            max-width: 100%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: linear-gradient(90deg, #fc8c5f 0, #fa7846 100%);
            font: bold 16px Inter, sans-serif;
            box-shadow: 0 18.4892px 46.223px -4.6223px rgba(250, 120, 70, .25), 0 2.77338px 0 #e87145;
            text-align: center;
            color: #fff;
            box-sizing: border-box;
            position: relative;
            position: fixed;
            right: 30px;
            bottom: 30px;
            z-index: 20;
            opacity: 0
        }

        .scroll-button i {
            font-size: 8px;
            margin-left: 9px
        }

        .scroll-button i {
            display: flex;
            justify-content: center;
            align-items: center;
            transform: rotate(-90deg);
            margin: 0
        }

        @media screen and (max-width:100em) {
            .menu {
                margin-right: 0
            }
        }

        @media screen and (max-width:75em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 30px), 1fr))
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 80px), 1fr))
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (3 - 0)) - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 30px), 1fr))
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 20px), 1fr))
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc((100% / (2 - 0)) - 20px), 1fr))
            }

            .services-photo img {
                height: 595px
            }
        }

        @media screen and (max-width:112.5em) {
            .menu {
                margin-right: 100px
            }
        }

        @media screen and (max-width:93.75em) {
            .section-frame {
                max-width: 1200px;
                padding: 0 20px
            }

            .services-photo a {
                width: 250px
            }

            .header-bar {
                max-width: 1200px
            }

            .header-social {
                display: none
            }

            .top-item__title {
                font-size: 16px
            }

            .kviz-item {
                padding: 40px;
                padding-top: 0
            }

            .kviz-stock {
                width: 350px
            }

            .stock-photo {
                height: 215px
            }

            .stock-list {
                display: flex;
                flex-wrap: wrap
            }

            .stock-item {
                width: 48%;
                padding: 15px;
                box-sizing: border-box
            }

            .stock-item img {
                max-width: 100px
            }

            .stock-item:last-child {
                margin: 0 auto
            }

            .kviz-finsh-photo {
                width: 460px
            }

            .stock-gift {
                left: 55px
            }

            .kviz-arrow {
                left: 3px;
                top: 30px
            }

            .services-item__title {
                min-height: 60px
            }

            .stock-gift {
                display: none
            }
        }

        @media screen and (max-width:84.375em) {
            .services-item__title {
                min-height: auto
            }

            .kviz-arrow {
                display: none
            }

            .services-photo img {
                height: 380px
            }

            .menu-list {
                width: 620px
            }

            .portraits-arrow {
                width: 110px;
                top: auto;
                right: 0;
                bottom: 40px
            }

            .portraits-prev {
                left: 0
            }

            .portraits-next {
                right: 0
            }
        }

        @media screen and (max-width:68.75em) {
            .file-save__title span {
                font-size: 9px
            }
        }

        @media screen and (max-width:61.25em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 80px), 1fr))
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 30px), 1fr))
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 2 - 20px), 1fr))
            }

            .section-frame {
                max-width: 740px
            }

            .file-save__title span {
                font-size: 11px
            }

            .ellipse {
                margin-top: -68px
            }

            .faq-title_tablet {
                display: block;
                margin-bottom: 30px
            }

            .step-counter {
                display: none
            }

            .kviz-item {
                flex-direction: column
            }

            .stock-full {
                display: none
            }

            .kviz-content {
                margin: 0 auto
            }

            .kviz-stock {
                margin: 0 auto
            }

            .kviz-button {
                justify-content: center
            }

            .kviz-group__title {
                margin-top: 20px
            }

            .kviz-finsh-photo {
                width: 360px
            }

            .submit-gift__arrow {
                display: none !important
            }

            .services-photo img {
                height: 485px
            }

            .header-item_pc {
                display: none
            }

            .header-button {
                margin-left: auto
            }

            .header-bar {
                padding: 20px 0
            }

            .header-cart i {
                width: 40px;
                height: 40px
            }

            .header-user {
                width: 40px;
                height: 40px
            }

            .burger {
                display: flex
            }

            .stock-full_tablet {
                display: block
            }

            .stock-list__title {
                display: block;
                text-align: center;
                font-size: 14px;
                line-height: 22px;
                margin-top: 10px
            }

            .stock-list {
                margin-top: 3px
            }

            .kviz-step {
                margin-bottom: 15px
            }

            .thanks {
                width: 700px
            }
        }

        @media screen and (max-width:43.75em) {
            .menu-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 30px), 1fr))
            }

            .services-photo a {
                height: 52px;
                font-size: 15px
            }

            .portraits-btn a {
                height: 52px;
                font-size: 15px
            }

            .kviz-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 80px), 1fr))
            }

            .kviz-next {
                height: 52px;
                font-size: 15px
            }

            .stock-list {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 11px), 1fr))
            }

            .kviz-finis-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 30px), 1fr))
            }

            .registration-sub {
                height: 52px;
                font-size: 15px
            }

            .kviz-sub {
                height: 52px;
                font-size: 15px
            }

            .kviz-thanks__info a {
                height: 52px;
                font-size: 15px
            }

            .top-btn {
                height: 52px;
                font-size: 15px
            }

            .popup-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .popup-grid {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .popup-photo__submit {
                height: 52px;
                font-size: 15px
            }

            .log-sub {
                height: 52px;
                font-size: 15px
            }

            .registration-group {
                grid-template-columns: repeat(auto-fill, minmax(calc(100% / 1 - 20px), 1fr))
            }

            .scroll-button {
                height: 52px;
                font-size: 15px
            }

            .section-frame {
                max-width: 375px;
                padding: 0 20px
            }

            .popup-frame {
                padding: 30px 15px;
                box-sizing: border-box
            }

            .popup-photo {
                width: 100%;
                padding: 60px 35px
            }

            .page-title {
                font-size: 24px;
                line-height: 29px
            }

            .photo-mokap {
                display: none
            }

            /* .kviz-input_pc {
                display: none
            } */

            .box-list {
                flex-wrap: wrap
            }

            .box-list .kviz-radio {
                margin-bottom: 9px
            }

            .kviz-input_mob {
                display: block;
                margin-top: 13px
            }

            .select-page {
                height: 52px;
                padding: 0 20px
            }

            .logo img {
                width: 130px
            }

            .kviz-politics {
                margin-top: 15px
            }

            .portraits-heading h1,
            .portraits-heading h2 {
                text-align: center;
                font-size: 36px;
                line-height: 43px
            }

            .portraits-heading p {
                text-align: center;
                margin-top: 15px;
                font-size: 16px;
                line-height: 22px
            }

            .portraits-title {
                text-align: center;
                font-size: 36px;
                line-height: 43px
            }

            .popup-photo__submit {
                margin-top: 20px
            }

            .portraits-slide {
                padding-top: 95px
            }

            .portraits-btn {
                flex-direction: column-reverse
            }

            .portraits-btn a {
                width: 100%;
                margin: 0;
                margin-bottom: 13px
            }

            .portraits-btn a:first-child {
                margin-bottom: 0
            }

            .portraits-gift {
                width: 100%;
                padding: 0;
                display: flex;
                align-items: flex-start;
                margin-top: 30px;
                background: 0 0
            }

            .portraits-gift img {
                display: none
            }

            .portraits-gift .portraits-gift__arrow {
                display: block;
                top: -20px;
                right: 35px;
                transform: rotate(50deg)
            }

            .portraits-gift p {
                max-width: 100%;
                padding-right: 40px;
                text-align: left;
                margin-top: 0
            }

            .gift-photo_mob {
                width: 31px;
                height: 33px;
                display: block !important;
                margin-right: 13px
            }

            .ellipse-arrow {
                width: 24px;
                height: 24px;
                font-size: 6px
            }

            .ellipse-arrow_white {
                border: 5px solid #fff
            }

            .ellipse {
                padding-top: 17px
            }

            .ellipse {
                margin-top: -25px
            }

            .page-title {
                font-size: 31px;
                line-height: 37px
            }

            .services-photo img {
                height: 225px
            }

            .services-item__title {
                font-size: 16px;
                line-height: 20px;
                min-height: 40px
            }

            .services-info p {
                font-size: 15px;
                line-height: 18px
            }

            .services-info__title {
                font-size: 16px;
                line-height: 19px
            }

            .services-list img {
                max-width: 100%;
                display: block
            }

            .portraits-arrow {
                width: 100%;
                box-sizing: border-box;
                left: 0;
                right: 0;
                margin: 0 auto;
                bottom: -100px
            }

            .portraits-arrow a {
                width: 36px;
                height: 36px;
                font-size: 12px
            }

            .portraits-prev {
                left: 0
            }

            .portraits-next {
                right: 0
            }

            .kviz-content {
                width: 100%
            }

            .file-save__title p {
                font-size: 15px
            }

            .file-save {
                background: rgba(255, 255, 255, .5)
            }

            .file-save__item {
                max-height: 52px
            }

            .page-input__item input {
                height: 52px;
                font-size: 15px
            }

            .faq-header h3 {
                font-size: 16px;
                line-height: 24px
            }

            .faq-body {
                font-size: 15px;
                line-height: 22px
            }

            .footer-list h3 span {
                display: flex
            }

            .footer-social_mob {
                display: flex;
                margin-top: 0
            }

            .kviz-item {
                padding: 0 20px 30px
            }

            .kviz-group__title {
                font-size: 16px;
                line-height: 24px;
                margin-bottom: 12px
            }

            .kviz-radio span {
                font-size: 15px;
                line-height: 22px
            }

            .kviz-stock {
                width: 100%
            }

            .stock-item {
                height: 90px;
                font-size: 11px;
                line-height: 15px
            }

            .stock-item img {
                max-height: 70px
            }

            .stock-item:last-child img {
                max-height: 60px
            }

            .kviz-button {
                margin-top: 20px;
                flex-direction: column
            }

            .kviz-skip {
                margin-left: 0;
                margin-top: 12px;
                font-size: 15px
            }

            .kviz-finis-group {
                width: 100%;
                margin-top: 0
            }

            .kviz-sub {
                width: 100%;
                margin: 0 auto
            }

            .submit-gift {
                margin-top: 20px
            }

            .kviz-finsh-photo {
                display: none
            }

            .kviz-thanks {
                margin: 130px 0 90px
            }

            .kviz-thanks__title {
                margin-top: 0
            }

            .kviz-thanks__title .h3_old {
                font-size: 24px;
                line-height: 29px
            }

            .kviz-thanks__title p {
                font-size: 16px;
                line-height: 22px
            }

            .kviz-thanks__icon {
                display: none
            }

            .kviz-thanks__info p {
                font-size: 13px
            }

            .kviz-thanks__info a {
                width: 100%
            }

            .burge-menu__item {
                font-size: 15px
            }

            .burge-menu__item h3 i {
                font-size: 8px
            }

            .burge-menu__item a {
                opacity: .75
            }

            .kviz-radio-descriptor img {
                display: block
            }

            .language ul {
                position: static;
                padding: 0;
                padding-top: 10px
            }

            .popup-login {
                width: 100%;
                padding: 35px 20px
            }

            .popup-registration {
                width: 100%;
                padding: 60px 20px
            }

            .registration-group {
                margin: 20px 0
            }

            .scroll-button {
                height: 40px;
                right: 20px;
                bottom: 20px
            }
        }

    </style>
