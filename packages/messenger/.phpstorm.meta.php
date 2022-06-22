<?php

namespace PHPSTORM_META;

override(
    \Symfony\Component\Messenger\Envelope::last(),
    map(["" => "@"])
);

override(
    \Symfony\Component\Messenger\Envelope::all(),
    map(["" => "@[]"])
);
