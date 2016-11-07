# Datatable Bundle for Symfony #
Datatable Bundle provides you a capabilitiy to handle Server Side Processing Datatables

## Creating your first Datatable Server Side ##
lets say you have an entity class like this

```
<?php
// src/AppBundle/Entity/People.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * People
 *
 * @ORM\Table(name="people")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PeopleRepository")
 */
class People
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return People
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return People
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
```

now, let's create some table. At this point, please make sure you already import jquery, datatable css and datatable js

```
// src/AppBundle/Resources/views/people.html.twig
<table id="table-people" class="table table-stripped">
    <thead>
        <tr>
            <td>NAME</td>
            <td>ADDRESS</td>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<script>
$(document).ready(function() {
    $("#table-people").DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            method: 'post',
            url: '{{ route('people_data') }}'
        },
        columns: [
            {data: 'name'},
            {data: 'address'},
        ],
    });
});
</script>
```

Ok, now we move to the Controller
```
// src/AppBundle/Controller/DefaultController.php

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/people", name="people")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle::people.html.twig', array());
    }

    /**
     * @Route("/people/data", name="people_data")
     * @Method("POST")
     */
    public function peopleDataAction(Request $request)
    {
        // call the service first.
        $datatable = $this->get('fys.dt.handle_request');
        
        // this is where the magic starts.
        // process() method has 3 parameters that should be filled.
        // first, Request $request Symfony\Component\HttpFoundation\Request Object
        // second, String Entity Class
        // third, array $columns, the ordered lists of field in the datatable table
        $response = $datatable->process($request, 'AppBundle:People', array('name', 'address'));
        
        // return JsonResponse object
        return new JsonResponse($response);
    }
}
```

you just create your first datatable server side
