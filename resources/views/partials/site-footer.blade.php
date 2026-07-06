<footer class="page-footer">
    <div class="container-xxl">
        <div class="page-footer__inner">
            <div>
                <strong>{{ $siteName }}</strong>
                @if(!empty($siteTagline))
                    <div>{{ $siteTagline }}</div>
                @endif
            </div>

            @if(!empty($siteCopyright))
                <div class="page-footer__copyright">{{ $siteCopyright }}</div>
            @endif
        </div>
    </div>
</footer>
