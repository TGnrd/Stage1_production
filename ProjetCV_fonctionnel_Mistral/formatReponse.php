<?php

$response_format = [
    "type" => "json_schema",
    "json_schema" => [
        "schema" => [
            "title" => "CV",
            "type" => "object",
            "properties" => [
                "nom" => [
                    "description" => "Remplis ce champ uniquement si une information textuelle explicite permet d'identifier clairement un prénom et un nom (ex. : une ligne isolée en haut du CV contenant deux mots). Écrit le nom et le prénom dans cette ordre. Il doit toujours y avoir au minimum deux mots pour remplir ce champ. Ne te base pas uniquement sur la taille de police ou la mise en forme pour deviner le nom si aucune mention claire n'est présente.",
                    "type" => "string"
                ],
                "age" => [
                    "description" => " L'âge est généralement indiqué par un nombre suivi de termes comme 'âge :', 'ans', ou 'années'. Concentre-toi sur les sections où des informations personnelles sont mentionnées, comme les sections 'Contact', 'Profil', ou 'Informations personnelles'. Si l'âge est explicitement écrit, utilise cette valeur sans faire de calculs supplémentaires et assure-toi qu'il s'agit bien de l'âge actuel. Assure-toi de vérifier que le nombre est bien associé à l'âge et non à d'autres informations comme une date ou une année. Si plusieurs mentions d'âge sont présentes, utilise celle qui est la plus clairement associée à l'âge actuel de la personne. Si tu ne trouve pas l'âge de la personne mais uniquement la date ou l'année de naissance, alors calcul l'âge de la personne en utilisant la date du jour fourni. Ne fais pas de suppositions et ne crée pas d'informations ; utilise uniquement les données exactes fournies dans le CV.",
                    "type" => "integer"
                ],
                "situation_familiale" => [
                    "enum" => ["marié", "mariée", "célibataire", "divorcé", "en couple", "pacsé", "veuf", "veuve"],
                    "description" => "doit être rempli uniquement si l’un des termes suivants apparaît : « marié », « mariée », « célibataire », « divorcé », « en couple », « pacsé ».",
                    "type" => "string"
                ],
                "nationalite" => [
                    "description" => "Identifi la nationalité. Attention, verifie bien qu'il s'agit de la nationalité et pas d'une langue parlée, ni avec le pays où vie la personne. ",
                    "type" => "string"
                ],
                "contact" => [
                    "description" => "Coordonnées personnelles extraites du document. Ne jamais inventer ni compléter ces données si elles ne sont pas clairement présentes. Si elle n'est pas clairement identifiable, ne rempli pas ce champ.",
                    "type" => "object",
                    "properties" => [
                        "email" => [
                            "pattern" => "^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$",
                            "description" => "Détecter les adresses email explicites, même si non accompagnées du mot 'email' : s’aider des symboles comme '@', enveloppe, ou icône de courriel. Ne prendre en compte que les formats corrects (ex. texte@domaine.ext). Fais attention à ne pas te tromper dans les chiffres potentiellement présents, fais attention à la police de caractère pour ne pas mélanger un chiffre avec un autre. Si tu ne vois pas de mail, ne le créer pas.",
                            "type" => "string"
                        ],
                        "telephone" => [
                            "pattern" => "^(\\+\\d{1,3}[ .-]?)?(\\(?\\d{1,4}\\)?[ .-]?){2,5}\\d{2,4}$",
                            "description" => "Numéro de téléphone reconnaissable par sa forme ou la présence de symboles (ex. téléphone, combiné, 📞, +33), même sans mot explicite. Extraire les suites de 10 à 14 chiffres, les normaliser au format international. Le numéro peut être écrit de différentes façons : sans espaces, avec des tirets entre plusieurs chiffres ou nombres, des points, ou des parenthèses. Si tu ne vois pas de numéro de téléphone, ne le crée pas",
                            "type" => "string",
                        ],
                        "adresse" => [
                            "description" => "Adresse postale complète si présente explicitement.",
                            "type" => "object",
                            "properties" => [
                                "rue" => [
                                    "description" => "Numéro et nom de rue.",
                                    "type" => "string"
                                ],
                                "complement" => [
                                    "description" => "Information complémentaire (étage, bâtiment) si indiquée.",
                                    "type" => "string"
                                ],
                                "ville" => [
                                    "description" => "Ville.",
                                    "type" => "string"
                                ],
                                "code_postal" => [
                                    "pattern" => "^[0-9]{5}$",
                                    "description" => "Code postal (5 chiffres).",
                                    "type" => "string"
                                ],
                                "region" => [
                                    "description" => "Région, département ou province si mentionné.",
                                    "type" => "string"
                                ],
                                "pays" => [
                                    "description" => "Pays.",
                                    "type" => "string"
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "permis" => [
                            "enum" => ["Permis A", "Permis B", "Permis C", "Permis D", "Permis E", "permis de conduire", "titulaire du permis", "j’ai mon permis"],
                            "description" => "indique uniquement si le texte mentionne explicitement la possession d’un permis de conduire. Cela inclut toutes les formulations comme : « permis B », « je possède le permis », « titulaire du permis », « permis de conduire », « j’ai mon permis », « permis en poche », « permis obtenu », « conduite accompagnée terminée », etc. Si rien n’indique clairement que la personne a le permis, ne renseigne pas ce champ..",
                            "type" => "string"
                        ],
                        "vehicule" => [
                            "enum" => ["Oui", "Non"],
                            "description" => "Le champ vehicule ne doit être rempli par oui uniquement si une mention explicite est présente dans le texte (ex. : 'je possède un véhicule', 'voiture personnelle', 'véhiculé(e)'). Si aucune information directe n’est fournie, laisse ce champ vide ou ne le renseigne pas..",
                            "type" => "string"
                        ]
                    ],
                    "required" => ["email", "telephone"],
                    "additionalProperties" => false
                ],
                "situation_professionnelle" => [
                    "description" => "Informations sur le statut et les disponibilités professionnelles.",
                    "type" => "object",
                    "properties" => [
                        "statut" => [
                            "enum" => [
                                "à la recherche d'emploi",
                                "salarié",
                                "indépendant",
                                "auto-entrepreneur",
                                "étudiant",
                                "en reconversion",
                                "demandeur d’emploi",
                                "en contrat d’apprentissage",
                                "en stage",
                                "retraité"
                            ],
                            "description" => "Valeur parmi celles listées, uniquement si mention explicite.",
                            "type" => "string"
                        ],
                        "poste_actuelle" => [
                            "description" => "identifie le poste actuel de la personne en utilisant des indicateurs tels que : 'maintenant', 'actuellement', 'à ce jour', 'en poste', 'en fonction', ou toute autre mention indiquant une activité professionnelle en cours. Fait attention de ne pas prendre le dernier poste effectué comme poste actuel, tu peux le savoir si il y a une date ou avec un indicateur comme ceux indiqués précédement. Si l’information n’apparaît pas clairement ou aucune information claire n’est présente, laisse ce champ vide.'.",
                            "type" => "string",
                        ],
                        "poste_recherche" => [
                            "description" => "Extrait le poste recherché si mentionné clairement dans le document. Le poste peut apparaître dans une phrase mais aussi de façon isolé en plus gros sur la page. Il peut y avoir plusieurs phrases de ce type, alors sois prudent pour ne pas en oublier. Attention, si la phrase est déjà inscrite autre part, ne recopie pas la phase une seconde fois. En cas de phrase commençant par 'Je suis à la recherche d'un emploi', 'Je recherche un emploi', 'à la recherche d'un emploi', 'Je suis à la recherche d'un stage', 'Je recherche un stage', 'à la recherche d'un stage',... sers-toi en comme repère. Ne mélange pas les expériences professionnelles avec le poste recherché, ne prends pas la dernière expérience professionnelle effectuée pour remplir ce champ. Si l'élément est absent, ne rien renseigner dans ce champ.",
                            "type" => "string"
                        ],
                        "annees_experiences" => [
                            "pattern" => "^[0-9]+\\s+ans$",
                            "description" => "Années d’expérience au format 'X ans' si explicitement indiqué.",
                            "type" => "string"
                        ],
                        "type_contrat_souhaite" => [
                            "description" => "Type de contrat souhaité ou mentionné (CDI, CDD, intérim, apprentissage, stage, etc.).",
                            "type" => "string"
                        ],
                        "disponibilites" => [
                            "description" => "Période de disponibilité en dates.",
                            "type" => "object",
                            "properties" => [
                                "date_debut" => [
                                    "description" => "Date de début de disponibilité, elle est souvent écrite assez proche du poste de recherche. Tous les formats de dates sont accèptées, même si elle n'est pas complète.",
                                    "type" => "string",
                                ],
                                "date_fin" => [
                                    "description" => "Date de fin de disponibilité, elle est souvent écrite assez proche du poste de recherche. Tous les formats de dates sont accèptées, même si elle n'est pas complète.",
                                    "type" => "string"
                                ]
                            ],
                            "additionalProperties" => false
                        ]
                    ],
                    "additionalProperties" => false
                ],
                "competences" => [
                    "description" => "Liste des compétences classées par catégorie. Une compétence ne doit jamais apparaître dans plusieurs catégories : choisis la plus représentative. Si des qualités personnelles ne rentrent dans aucune autre catégorie, inclue-les dans le champ qualites. N'hésite pas à récupérer les descriptions des compétences si elles sont notées.",
                    "type" => "object",
                    "properties" => [
                        "techniques" => [
                            "description" => "Compétences métiers techniques (ex. épilation, soins du corps, massage). Écrit bien toutes les compétances qui sont notées sur le CV, ne te limite pas à la catégorie 'Compétence' du CV, cherche aussi dans les compétences données dans la partie 'experiences professionnelles' par exemple. N'hésite pas à récupérer les descriptions des compétences si elles sont notées.",
                            "type" => "array",
                            "items" => ["type" => "string"],
                        ],
                        "logiciels_outils" => [
                            "description" => "Noms de logiciels, applications ou outils numériques explicitement mentionnés (ex. Excel, Word, CRM). Inclut aussi les réseaux sociaux, que les noms des réseaux soit écrit ou pas. N'hésite pas à récupérer les descriptions des compétences si elles sont notées.",
                            "type" => "array",
                            "items" => ["type" => "string"],
                        ],
                        "methodologies" => [
                            "description" => "Méthodes de travail ou normes (ex. HACCP, méthode Agile). N'hésite pas à récupérer les descriptions des compétences si elles sont notées.",
                            "type" => "array",
                            "items" => ["type" => "string"],
                        ]
                    ],
                    "additionalProperties" => false
                ],
                "aptitudes_professionnelles" => [
                    "description" => "Qualités organisationnelles, relationnelles et autres.",
                    "type" => "object",
                    "properties" => [
                        "organisationnelles" => [
                            "description" => "Qualités organisationnelles (rigueur, autonomie, gestion du temps).",
                            "type" => "array",
                            "items" => ["type" => "string"],
                        ],
                        "relationnelles" => [
                            "description" => "Qualités relationnelles (communication, travail d’équipe).",
                            "type" => "array",
                            "items" => ["type" => "string"]
                        ],
                        "autres" => [
                            "description" => "Autres qualités personnelles (curieuse, rigoureuse, dévouée), si elles n’entrent pas dans une autre catégorie.",
                            "type" => "array",
                            "items" => ["type" => "string"]
                        ]
                    ],
                    "additionalProperties" => false
                ],
                "motivation" => [
                    "description" => "Identifie et extrais tous les mots, les groupe de mots et les phrases exprimant explicitement l’intérêt pour le métier, les objectifs professionnels, des slogans, des phrases de dévouement pour son travail et les formules de politesse. Recopie bien toute la/les phrases présente(s) et pas qu'une partie. Rassemble ces phrases dans un tableau « motivation » du JSON, sans doublons et sans inventer ou compléter ce qui n’est pas explicitement présent. Ne prends pas pour les motivations les centres d'intérêt/loisir, les qualités et aptitudes professionnelles.",
                    "type" => "array",
                    "items" => ["type" => "string"]
                ],
                "experiences" => [
                    "description" => "Liste des expériences professionnelles avec dates et détails. Reste bien dans le cadre des experiences professionnelles, n'ajoute pas un poste en dehors de ce cadre. Met bien les experiences professionnelles dans l'ordre chronologique.",
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "poste" => [
                                "description" => "Intitulé du poste.  ",
                                "type" => "string",
                            ],
                            "entreprise" => [
                                "description" => "Nom de l’entreprise.",
                                "type" => "string",
                            ],
                            "date_debut" => [
                                "description" => "Date de début (même si placée au-dessus ou éloignée du titre). La date doit toujours apparaître sous le format suivant : jj/mm/année, si le jour n'est pas spécifié, la date doit être écrite dans ce format : mm/année. Si seulement l'année apparaît écrit uniquement l'année. Dans le cas où le jour n'est pas spécifié ne prend pas le premier jour du mois comme jour.",
                                "type" => "string",
                            ],
                            "date_fin" => [
                                "description" => "Date de fin (même si placée au-dessus ou éloignée du titre). La date doit toujours apparaître sous le format suivant : jj/mm/année, si le jour n'est pas spécifié, la date doit être écrite dans ce format : mm/année. Si seulement l'année apparaît écrit uniquement l'année. Il peut ne pas y avoir de date, mais un ou des mot(s) permettant d'identifier si le poste est toujours d'actualité (maintenant, actuellement, en cours,...), si c'est le cas écrit ce(s) mot(s). Dans le cas où le jour n'est pas spécifié ne prend pas le premier jour du mois comme jour.",
                                "type" => "string",
                            ],
                            "duree" => [
                                "description" => "Si il y a une durée contnant 'depuis ..., employé depuis ..., etc' ou nimporte qu'elle autre expression qui sous-entends que l'emploi est toujours actuel, alors écrit-le. S'il n'y en a pas, n'écrit rien dans ce champ.",
                                "type" => "string",
                            ],
                            "lieu_d'activite" => [
                                "description" => "Lieu géographique de l’activité.",
                                "type" => "string",
                            ],
                            "type_contrat" => [
                                "enum" => ["CDI", "CDD", "stage", "intérim", "apprentissage", "alternant"],
                                "description" => "Identifie  les types de contrats (CDI, CDD, intérim, stage, etc,...), ils peuvent être à côté de la durée, du nom de l'entreprise, de l'année d'exercice, des activitées ou dans le nom de l'expérience. Dès qu'il y a un nom de contrat donné inscrit-le dans le champ.",
                                "type" => "string"
                            ],
                            "activites_descriptions" => [
                                "description" => "Listes et descriptions des tâches ou activités réalisées. Ce champ doit être remplit avec toutes les informations complémentaires sur le poste,  c'est-à-dire les actions réalisées, les compétences travaillées, etc... Si l'information n'est pas présente dans le CV, alors n'affiche pas ce champ.",
                                "type" => "array",
                                "items" => ["type" => "string"]
                            ]
                        ],
                        "additionalProperties" => false
                    ]
                ],
                "formations" => [
                    "description" => "Liste des formations avec dates, établissements et mentions.",
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "intitule" => [
                                "description" => "Intitulé de la formation.",
                                "type" => "string",
                            ],
                            "etablissement" => [
                                "description" => "Nom de l’établissement.",
                                "type" => "string",
                            ],
                            "date_debut" => [
                                "description" => "Date de début (même si placée au-dessus ou éloignée du titre). La date doit toujours apparaître sous le format suivant : jj/mm/année, si le jour n'est pas spécifié, la date doit être écrite dans ce format : mm/année. Si seulement l'année apparaît écrit uniquement l'année. Dans le cas où le jour n'est pas spécifié ne prend pas le premier jour du mois comme jour.",
                                "type" => "string",
                            ],
                            "date_fin" => [
                                "description" => "Date de fin (même si placée au-dessus ou éloignée du titre). La date doit toujours apparaître sous le format suivant : jj/mm/année, si le jour n'est pas spécifié, la date doit être écrite dans ce format : mm/année. Si seulement l'année apparaît écrit uniquement l'année. Il peut ne pas y avoir de date, mais un ou des mot(s) permettant d'identifier si le poste est toujours d'actualité (maintenant, actuellement, en cours,...), si c'est le cas écrit ce(s) mot(s). Dans le cas où le jour n'est pas spécifié ne prend pas le premier jour du mois comme jour.",
                                "type" => "string",
                            ],
                            "duree" => [
                                "description" => "Si il y a une durée contnant 'depuis ..., employé depuis ..., etc' ou nimporte qu'elle autre expression qui sous-entends que l'emploi est toujours actuel, alors écrit-le. S'il n'y en a pas, n'écrit rien dans ce champ.",
                                "type" => "string",
                            ],
                            "option" => [
                                "description" => "Option ou spécialité si mentionnée.",
                                "type" => "string",
                            ],
                            "specialites" => [
                                "description" => "Spécialités listées dans la formation.",
                                "type" => "array",
                                "items" => ["type" => "string"],
                            ],
                            "mention" => [
                                "description" => "Mention du diplôme, uniquement l’un des libellés listés.",
                                "type" => "string",
                                "enum" => ["assez bien", "bien", "très bien", "félicitations du jury"],
                            ]
                        ],
                        "additionalProperties" => false
                    ]
                ],
                "langues" => [
                    "description" => "Liste des langues avec niveaux original et normalisé.",
                    "type" => "array",
                    "items" => [
                        "type" => "object",
                        "properties" => [
                            "langue" => [
                                "description" => "Nom de la langue.",
                                "type" => "string",
                            ],
                            "niveau_original" => [
                                "description" => "Niveau tel qu’écrit dans le CV (ex : bilingue, A2, 4/5). Le niveau doit obligatoirement être écrit avec des lettres, chiffres ou mots. Si aucun texte n’est écrit, ne renseigne pas ce champ. Ignore tout niveau exprimé uniquement par des symboles (ex : ✅, ●, ☑). Ne remplis le champ niveau_original que s’il contient au moins une lettre ou un chiffre. N'invente pas le niveau si tu n'es pas sûr. ",
                                "pattern" => "[a-zA-Z0-9]",
                                "type" => "string"
                            ],

                            "niveau_normalise" => [
                                "type" => "integer",
                                "minimum" => 1,
                                "maximum" => 5,
                                "description" => "Niveau converti en entier (1=faible, 5=excellent). Essaye s'interpéter avec le résultat du niveau original pour trouver la note la plus proche du niveau annoncé."
                            ]
                        ],
                        "required" => ["langue"],
                        "additionalProperties" => false
                    ]
                ],
                "centres_interet" => [
                    "description" => "Analyse l'image ou le texte pour identifier les centres d'intérêt. Recherche des illustrations, icônes ou mots indiquant des hobbies ou passions. Catégorise-les en groupes comme sports, arts ou cuisine. Décris brièvement chaque centre d'intérêt trouvé. Base-toi uniquement sur les informations visibles ou mentionnées. Présente les résultats sous forme de liste. Récupère les informations uniquement si elle se trouve dans un champ 'loisir', 'centres d'intérêts', 'passion' ou 'activité', veille bien à ce que le champ soit précédé de l'uns de ces mots. Si le nom est 'autre', vérifie bien qu'il s'agit de centre d'intérêt avant de recopier.",
                    "type" => "array",
                    "items" => ["type" => "string"],
                ]
            ],
            "additionalProperties" => false
        ],
        "name" => "cv",
        "strict" => true
    ]
];
