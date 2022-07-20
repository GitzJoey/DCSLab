import _ from 'lodash';

export var userContext = () => {
    return {
        hId:'VrWKo01AGYBp',
        uuid:'f42b5770-233a-400f-a272-7c0ba974c88a',
        name:'GitzJoey',
        email:'gitzjoey@yahoo.com',
        email_verified:true,
        password_expiry_day:362,
        profile:{
            first_name:'GitzJoey',
            last_name:'',
            address:null,
            city:null,
            postal_code:null,
            country:'Singapore',
            status:'ACTIVE',
            tax_id:'',
            ic_num:'',
            img_path:null,
            remarks:null
        },
        roles:[
            {
                hId:'NKWr256kaq4J',
                display_name:'Administrator',
                permissions:[
                    {hId:'VrWKo01AGYBp',display_name:'Read Profile'},
                    {hId:'NKWr256kaq4J',display_name:'Update Profile'},
                    {hId:'gaG0kemkZO8Q',display_name:'Create Messaging'},
                    {hId:'yjRKAnEoVerJ',display_name:'Read Messaging'},
                    {hId:'nbgOodz2LB7R',display_name:'Update Messaging'},
                    {hId:'0VGvAXxA1PRq',display_name:'Delete Messaging'},
                    {hId:'3Ye0AOJor1RL',display_name:'Read Settings'},
                    {hId:'0B5E23G2XmQp',display_name:'Update Settings'},
                    {hId:'5rDyop62P3nL',display_name:'Create Users'},
                    {hId:'dQYLkZ12DK6N',display_name:'Read Users'},
                    {hId:'GEqQkqykvRwL',display_name:'ReadAny Users'},
                    {hId:'jnrq2GVAeJRw',display_name:'Update Users'}
                ],
                permissions_description:'Read Profile,Update Profile,Create Messaging,Read Messaging,Update Messaging,Delete Messaging,Read Settings,Update Settings,Create Users,Read Users,ReadAny Users,Update Users'
            },
            {
                hId:'yjRKAnEoVerJ',
                display_name:'POS-owner',
                permissions:[
                    {hId:'QXmJkwloN46r',display_name:'Create Companies'},
                    {hId:'REdBA83obmn5',display_name:'Read Companies'},
                    {hId:'V8QmoVakxl1a',display_name:'ReadAny Companies'},
                    {hId:'j4wRoa5olPmp',display_name:'Update Companies'},
                    {hId:'QBZq26n2MpX7',display_name:'Delete Companies'},
                    {hId:'nq5Lkvq2Ovr7',display_name:'Create Branches'},
                    {hId:'K3GLkYakPD0e',display_name:'Read Branches'},
                    {hId:'l19J2WaoO6MX',display_name:'ReadAny Branches'},
                    {hId:'n7NG2RZ2mJj3',display_name:'Update Branches'},
                    {hId:'eWPv2Me2anKp',display_name:'Delete Branches'},
                    {hId:'84Llk13Am3Vx',display_name:'Create Employees'},
                    {hId:'8rgl276AEGYv',display_name:'Read Employees'},
                    {hId:'zR7ZoxV2jgOe',display_name:'ReadAny Employees'},
                    {hId:'mvqJA4PAXBw8',display_name:'Update Employees'},
                    {hId:'QbNX2PL21dJ6',display_name:'Delete Employees'},
                    {hId:'pZeEArjA8BXn',display_name:'Create Warehouses'},
                    {hId:'vVJYoDpo7MZy',display_name:'Read Warehouses'},
                    {hId:'p1zZALQ2e0bE',display_name:'ReadAny Warehouses'},
                    {hId:'MgX0k942xJZ3',display_name:'Update Warehouses'},
                    {hId:'0JpL2gaoN7bl',display_name:'Delete Warehouses'},
                    {hId:'Krpb2bB2Y4y1',display_name:'Create Suppliers'},
                    {hId:'8lz5oK7ow3d0',display_name:'Read Suppliers'},
                    {hId:'bDgGoz1kxpne',display_name:'ReadAny Suppliers'},
                    {hId:'1w56ABMoMEdq',display_name:'Update Suppliers'},
                    {hId:'Yn3Z2JWkjyOl',display_name:'Delete Suppliers'},
                    {hId:'9pqXkNgole8K',display_name:'Create Products'},
                    {hId:'eXGJ2ljoa1MY',display_name:'Read Products'},
                    {hId:'RZ7eoQDkW5GJ',display_name:'ReadAny Products'},
                    {hId:'P1x0AEz2MlaD',display_name:'Update Products'},
                    {hId:'48dV2y7ozbZN',display_name:'Delete Products'},
                    {hId:'3NmgkmekJl87',display_name:'Create Brands'},
                    {hId:'nqJKAjjNARaZ',display_name:'Read Brands'},
                    {hId:'VrWKo011AGYB',display_name:'ReadAny Brands'},
                    {hId:'NKWr25N6kaq4',display_name:'Update Brands'},
                    {hId:'gaG0keDm2ZO8',display_name:'Delete Brands'},
                    {hId:'yjRKAnnEAVer',display_name:'Create Productgroups'},
                    {hId:'nbgOodLzoLB7',display_name:'Read Productgroups'},
                    {hId:'0VGvAXMx21PR',display_name:'ReadAny Productgroups'},
                    {hId:'3Ye0AOLJ2r1R',display_name:'Update Productgroups'},
                    {hId:'0B5E23yGoXmQ',display_name:'Delete Productgroups'},
                    {hId:'5rDyopG6kP3n',display_name:'Create Services'},
                    {hId:'dQYLkZy12DK6',display_name:'Read Services'},
                    {hId:'GEqQkqwyAvRw',display_name:'ReadAny Services'},
                    {hId:'jnrq2G4VkeJR',display_name:'Update Services'},
                    {hId:'QXmJkwmlAN46',display_name:'Delete Services'},
                    {hId:'REdBA8N3kbmn',display_name:'Create Units'},
                    {hId:'V8QmoVYakxl1',display_name:'Read Units'},
                    {hId:'j4wRoaY5olPm',display_name:'ReadAny Units'},
                    {hId:'QBZq26Bn2MpX',display_name:'Update Units'},
                    {hId:'nq5LkvVqkOvr',display_name:'Delete Units'},
                    {hId:'K3GLkYEakPD0',display_name:'Create Purchaseorders'},
                    {hId:'l19J2WXakO6M',display_name:'Read Purchaseorders'},
                    {hId:'n7NG2RlZomJj',display_name:'ReadAny Purchaseorders'},
                    {hId:'eWPv2MDeAanK',display_name:'Update Purchaseorders'},
                    {hId:'84Llk1N3om3V',display_name:'Delete Purchaseorders'},
                    {hId:'8rgl27w62EGY',display_name:'Create Salesorders'},
                    {hId:'zR7ZoxmVAjgO',display_name:'Read Salesorders'},
                    {hId:'mvqJA4GPoXBw',display_name:'ReadAny Salesorders'},
                    {hId:'QbNX2PNLo1dJ',display_name:'Update Salesorders'},
                    {hId:'pZeEAryjo8BX',display_name:'Delete Salesorders'}
                ],
                permissions_description:'Create Companies,Read Companies,ReadAny Companies,Update Companies,Delete Companies,Create Branches,Read Branches,ReadAny Branches,Update Branches,Delete Branches,Create Employees,Read Employees,ReadAny Employees,Update Employees,Delete Employees,Create Warehouses,Read Warehouses,ReadAny Warehouses,Update Warehouses,Delete Warehouses,Create Suppliers,Read Suppliers,ReadAny Suppliers,Update Suppliers,Delete Suppliers,Create Products,Read Products,ReadAny Products,Update Products,Delete Products,Create Brands,Read Brands,ReadAny Brands,Update Brands,Delete Brands,Create Productgroups,Read Productgroups,ReadAny Productgroups,Update Productgroups,Delete Productgroups,Create Services,Read Services,ReadAny Services,Update Services,Delete Services,Create Units,Read Units,ReadAny Units,Update Units,Delete Units,Create Purchaseorders,Read Purchaseorders,ReadAny Purchaseorders,Update Purchaseorders,Delete Purchaseorders,Create Salesorders,Read Salesorders,ReadAny Salesorders,Update Salesorders,Delete Salesorders'
            }
        ],
        roles_description:'Administrator,POS-owner',
        selected_roles:['NKWr256kaq4J','yjRKAnEoVerJ'],
        companies:[

        ],
        settings:[
            {key:'PREFS.THEME',value:'side-menu-light-full'},
            {key:'PREFS.DATE_FORMAT',value:'yyyy_MM_dd'},
            {key:'PREFS.TIME_FORMAT',value:'hh_mm_ss'}
        ],
        selected_settings:{
            theme:'side-menu-light-full',
            dateFormat:'yyyy_MM_dd',
            timeFormat:'hh_mm_ss'
        }
    };
}

export var sideMenu = () => {
    return [
        {
            icon:"HomeIcon",
            pageName:"side-menu-dashboard",
            title:"components.menu.dashboard",
            subMenu:[
                {
                    icon:"",pageName:"side-menu-dashboard-maindashboard",
                    title:"components.menu.main-dashboard",active:true
                }
            ],
            active:true,
            activeDropdown:true
        },
        {
            icon:"UmbrellaIcon",
            pageName:"side-menu-company",
            title:"components.menu.company",
            subMenu:[
                {
                    icon:"",
                    pageName:"side-menu-company-company",
                    title:"components.menu.company-company",
                    active:undefined
                }
            ],
            active:false,
            activeDropdown:false
        },
        {
            icon:"CpuIcon",
            pageName:"side-menu-administrator",
            title:"components.menu.administrator",
            subMenu:[
                {
                    icon:"",
                    pageName:"side-menu-administrator-user",
                    title:"components.menu.administrator-user",
                    active:undefined
                }
            ],
            active:false,
            activeDropdown:false
        }
    ];
}