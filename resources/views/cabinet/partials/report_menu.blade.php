<ul id="nav">
    <li @if(request()->is('cabinet/reports*')) class="current" @endif><a href="http://www.ndesign-studio.com">По машинам</a>
        <ul>
            {{--<li><a href="http://www.ndesign-studio.com">N.Design Studio</a>
                <ul>
                    <li><a href="http://www.ndesign-studio.com/portfolio">Portfolio</a></li>
                    <li><a href="http://www.ndesign-studio.com/wp-themes">WordPress Themes</a></li>
                    <li><a href="http://www.ndesign-studio.com/wallpapers">Wallpapers</a></li>
                    <li><a href="http://www.ndesign-studio.com/tutorials">Illustrator Tutorials</a></li>
                </ul>
            </li>--}}
            {{--<li><a href="http://www.webdesignerwall.com">Web Designer Wall</a>
                <ul>
                    <li><a href="http://jobs.webdesignerwall.com">Design Job Wall</a></li>
                </ul>
            </li>--}}
            <li><a @if(request()->is('cabinet/reports*')) class="active_report_menu" @endif href="{{ route('cabinet.report.index') }}">Список пропусков</a></li>
            <li><a href="http://bestwebgallery.com">Best Web Gallery</a></li>
        </ul>
    </li>
    <li><a href="#">По сотрудникам</a>
        <ul>
            <li><a href="#">Sales</a></li>
            <li><a href="#">Another Link</a></li>
            {{--<li><a href="#">Department</a>
                <ul>
                    <li><a href="#">Sub-Level Item</a></li>
                    <li><a href="#">Sub-Level Item</a></li>
                    <li><a href="#">Sub-Level Item</a></li>
                </ul>
            </li>--}}
        </ul>
    </li>
    {{--<li><a href="#">About</a></li>
    <li><a href="#">Contact Us</a></li>--}}
</ul>
