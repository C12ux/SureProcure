<?php

/*
| Criteria used in every Bid Evaluation Form. Each group's weights add up to 100.
| Scores are 0 to 100. The weights are copied onto each saved score, so changing
| them here later never alters evaluations that were already done.
*/

return [

    'criteria' => [

        'technical' => [
            ['name' => 'Meets specifications', 'weight' => 40],
            ['name' => 'Experience and references', 'weight' => 30],
            ['name' => 'Delivery and lead time', 'weight' => 30],
        ],

        'commercial' => [
            ['name' => 'Price', 'weight' => 60],
            ['name' => 'Payment terms', 'weight' => 25],
            ['name' => 'Warranty and after-sales support', 'weight' => 15],
        ],
    ],
];
