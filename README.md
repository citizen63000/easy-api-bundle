# easy-api-bundle
Symfony bundle to easily make api
Inspired by work of [DarwinOnLine](https://github.com/DarwinOnLine)

Configuration (everything is optionnal) :

easy_api:
  authentication: true
  user_class: AppBundle\Entity\User\User
  user_tracking :
    enable: false
    connection_history_class: AppBundle\Entity\User\ConnectionHistory
  inheritance:
    entity: 'CoreBundle\Entity\AbstractEntity'
    entityReferential: 'CoreBundle\Entity\AbstractReferential'
    form: 'CoreBundle\Form\Type\AbstractCoreType'
    repository: 'CoreBundle\Form\Type\AbstractRepository'
