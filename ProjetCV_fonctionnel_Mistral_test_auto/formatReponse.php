<?php

$response_format = [
    "type" => "json_schema",
    "json_schema" => [
        "schema" => [
            "title" => "CV",
            "type" => "object",
            "properties" => [
                "nom" => [
                    "type" => ["string", "null"]
                ],
                "age" => [
                    "type" => ["integer", "null"]
                ],
                "situation_familiale" => [
                    "type" => ["string", "null"],
                    "enum" => ["marié", "mariée", "célibataire", "divorcé", "en couple", "pacsé", "veuf", "veuve"]
                ],
                "nationalite" => [
                    "type" => ["string", "null"]
                ],
                "contact" => [
                    "type" => ["object", "null"],
                    "properties" => [
                        "email" => ["type" => ["string", "null"]],
                        "telephone" => ["type" => ["string", "null"]],
                        "adresse" => [
                            "type" => ["object", "null"],
                            "properties" => [
                                "rue"         => ["type" => ["string", "null"]],
                                "complement"  => ["type" => ["string", "null"]],
                                "ville"       => ["type" => ["string", "null"]],
                                "code_postal" => ["type" => ["string", "null"]],
                                "region"      => ["type" => ["string", "null"]],
                                "pays"        => ["type" => ["string", "null"]]
                            ],
                            "additionalProperties" => false
                        ],
                        "permis" => [
                            "type" => ["string", "null"],
                            "enum" => ["Permis A", "Permis B", "Permis C", "Permis D", "Permis E", "permis de conduire", "titulaire du permis", "j'ai mon permis"]
                        ],
                        "vehicule" => [
                            "type" => ["string", "null"],
                            "enum" => ["Oui", "Non"]
                        ]
                    ],
                    "additionalProperties" => false
                ],
                "situation_professionnelle" => [
                    "type" => ["object", "null"],
                    "properties" => [
                        "statut" => [
                            "type" => ["string", "null"],
                            "enum" => ["à la recherche d'emploi", "salarié", "indépendant", "auto-entrepreneur", "étudiant", "en reconversion", "demandeur d'emploi", "en contrat d'apprentissage", "en stage", "retraité"]
                        ],
                        "poste_actuelle"       => ["type" => ["string", "null"]],
                        "poste_recherche"      => ["type" => ["string", "null"]],
                        "annees_experiences"   => ["type" => ["string", "null"]],
                        "type_contrat_souhaite"=> ["type" => ["string", "null"]],
                        "disponibilites"       => [
                            "type" => ["object", "null"],
                            "properties" => [
                                "date_debut" => ["type" => ["string", "null"]],
                                "date_fin"   => ["type" => ["string", "null"]]
                            ],
                            "additionalProperties" => false
                        ]
                    ],
                    "additionalProperties" => false
                ],
                "competences" => [
                    "type" => ["object", "null"],
                    "properties" => [
                        "techniques"       => ["type" => ["array", "null"], "items" => ["type" => "string"]],
                        "logiciels_outils" => ["type" => ["array", "null"], "items" => ["type" => "string"]],
                        "methodologies"    => ["type" => ["array", "null"], "items" => ["type" => "string"]]
                    ],
                    "additionalProperties" => false
                ],
                "aptitudes_professionnelles" => [
                    "type" => ["object", "null"],
                    "properties" => [
                        "organisationnelles" => ["type" => ["array", "null"], "items" => ["type" => "string"]],
                        "relationnelles"     => ["type" => ["array", "null"], "items" => ["type" => "string"]],
                        "autres"             => ["type" => ["array", "null"], "items" => ["type" => "string"]]
                    ],
                    "additionalProperties" => false
                ],
                "motivation" => [
                    "type" => ["array", "null"],
                    "items" => ["type" => "string"]
                ],
                "experiences" => [
                    "type" => ["array", "null"],
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "poste"                  => ["type" => ["string", "null"]],
                            "entreprise"             => ["type" => ["string", "null"]],
                            "date_debut"             => ["type" => ["string", "null"]],
                            "date_fin"               => ["type" => ["string", "null"]],
                            "duree"                  => ["type" => ["string", "null"]],
                            "lieu_d'activite"        => ["type" => ["string", "null"]],
                            "type_contrat"           => [
                                "type" => ["string", "null"],
                                "enum" => ["CDI", "CDD", "stage", "intérim", "apprentissage", "alternant"]
                            ],
                            "activites_descriptions" => ["type" => ["array", "null"], "items" => ["type" => "string"]]
                        ],
                        "additionalProperties" => false
                    ]
                ],
                "formations" => [
                    "type" => ["array", "null"],
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "intitule"    => ["type" => ["string", "null"]],
                            "etablissement"=> ["type" => ["string", "null"]],
                            "date_debut"  => ["type" => ["string", "null"]],
                            "date_fin"    => ["type" => ["string", "null"]],
                            "duree"       => ["type" => ["string", "null"]],
                            "option"      => ["type" => ["string", "null"]],
                            "specialites" => ["type" => ["array", "null"], "items" => ["type" => "string"]],
                            "mention"     => [
                                "type" => ["string", "null"],
                                "enum" => ["assez bien", "bien", "très bien", "félicitations du jury"]
                            ]
                        ],
                        "additionalProperties" => false
                    ]
                ],
                "langues" => [
                    "type" => ["array", "null"],
                    "items" => [
                        "type" => "object",
                        "required" => ["langue"],
                        "properties" => [
                            "langue"           => ["type" => ["string", "null"]],
                            "niveau_original"  => ["type" => ["string", "null"]],
                            "niveau_normalise" => [
                                "type"    => ["integer", "null"],
                                "minimum" => 1,
                                "maximum" => 5
                            ]
                        ],
                        "additionalProperties" => false
                    ]
                ],
                "centres_interet" => [
                    "type" => ["array", "null"],
                    "items" => ["type" => "string"]
                ]
            ],
            "additionalProperties" => false
        ],
        "name"   => "cv",
        "strict" => false
    ]
];
