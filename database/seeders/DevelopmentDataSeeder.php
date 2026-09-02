<?php

namespace Database\Seeders;

use App\Models\{Availability, Certification, Company, Mission, MissionRequirement, Rating, Role, Skill, User, WorkerProfile, WorkerRequest};
use Illuminate\Database\Seeder;

class DevelopmentDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers(Role::pluck('id', 'name')->all());
        $companies = $this->seedCompanies($users);
        $this->linkCompanyUsers($users, $companies);
        $skills = $this->seedLookups(Skill::class, $this->skills());
        $certifications = $this->seedLookups(Certification::class, $this->certifications());
        $workers = $this->seedWorkers($users, $companies, $skills, $certifications);
        $this->seedAvailability($workers);
        $missions = $this->seedMissions($users, $companies);
        $this->seedActivity($users, $companies, $workers, $missions);
    }

    private function seedUsers(array $roles): array
    {
        $accounts = [
            'admin'=>['Alex Morgan','admin@constructionworkforce.test','administrator'], 'admin_operations'=>['Cameron Reid','cameron.reid@constructionworkforce.test','administrator'], 'admin_support'=>['Taylor Brooks','taylor.brooks@constructionworkforce.test','administrator'],
            'northstar_owner'=>['Sarah Bennett','sarah.bennett@northstar.test','company_owner'], 'northstar_planner'=>['Marcus Lee','marcus.lee@northstar.test','planning_manager'],
            'summit_owner'=>['Priya Shah','priya.shah@summit.test','company_owner'], 'summit_planner'=>['Daniel Ross','daniel.ross@summit.test','planning_manager'],
            'prairie_owner'=>['Nolan Fraser','nolan.fraser@prairie.test','company_owner'], 'prairie_planner'=>['Kendra Mills','kendra.mills@prairie.test','planning_manager'],
            'iron_owner'=>['Tessa Ward','tessa.ward@ironridge.test','company_owner'], 'iron_planner'=>['Grant Wilson','grant.wilson@ironridge.test','planning_manager'],
            'cedar_owner'=>['Elena Petrov','elena.petrov@cedarpeak.test','company_owner'], 'cedar_planner'=>['Owen Hart','owen.hart@cedarpeak.test','planning_manager'],
            'maria'=>['Maria Chen','maria.chen@independent.test','self_employed'], 'jordan'=>['Jordan Lewis','jordan.lewis@independent.test','self_employed'], 'evan'=>['Evan Cole','evan.cole@independent.test','self_employed'],
        ];
        foreach ([['Aiden Park','aiden.park'],['Brooke Turner','brooke.turner'],['Caleb Young','caleb.young'],['Diana Flores','diana.flores'],['Elliot James','elliot.james'],['Farah Malik','farah.malik'],['Gavin Price','gavin.price'],['Hailey Stone','hailey.stone'],['Isaac Grant','isaac.grant'],['Jenna Roy','jenna.roy'],['Kyle Adams','kyle.adams'],['Leah Ford','leah.ford'],['Micah Bell','micah.bell'],['Nina Cooper','nina.cooper'],['Omar Diaz','omar.diaz'],['Paige Kelly','paige.kelly'],['Quinn Walker','quinn.walker'],['Riley Scott','riley.scott'],['Sofia Grant','sofia.grant'],['Theo Martin','theo.martin'],['Uma Patel','uma.patel'],['Victor Chen','victor.chen'],['Willa Gray','willa.gray'],['Xavier Moore','xavier.moore'],['Yasmin Ali','yasmin.ali'],['Zachary Reed','zachary.reed'],['Amelia Knight','amelia.knight'],['Brandon Fox','brandon.fox'],['Chloe Davis','chloe.davis'],['Declan Murphy','declan.murphy'],['Erica Long','erica.long'],['Finn Russell','finn.russell'],['Gemma White','gemma.white'],['Henry Lawson','henry.lawson']] as $index => [$name,$prefix]) {
            $accounts['independent_'.$index] = [$name, $prefix.'@independent.test', 'self_employed'];
        }
        $users=[];
        foreach ($accounts as $key => [$name,$email,$role]) {
            $users[$key]=User::updateOrCreate(['email'=>$email], ['name'=>$name,'password'=>'password','role_id'=>$roles[$role],'company_id'=>null,'phone'=>sprintf('403-555-%04d',100+count($users)),'language'=>'en','timezone'=>'America/Edmonton','email_notifications'=>true,'sms_notifications'=>false,'mission_alerts'=>true,'email_verified_at'=>now()]);
        }
        return $users;
    }

    private function seedCompanies(array $users): array
    {
        $definitions=['northstar'=>['Northstar Build Group','403-555-2100','1180 8 Avenue SW, Calgary, AB','northstar_owner'],'summit'=>['Summit Mechanical & Electrical','780-555-2200','10420 101 Street NW, Edmonton, AB','summit_owner'],'prairie'=>['Prairie Concrete & Civil','403-555-2300','6500 67 Street, Red Deer, AB','prairie_owner'],'iron'=>['Iron Ridge Steelworks','403-555-2400','4321 61 Avenue SE, Calgary, AB','iron_owner'],'cedar'=>['Cedar Peak Exteriors','780-555-2500','16815 111 Avenue NW, Edmonton, AB','cedar_owner']];
        $companies=[];
        foreach ($definitions as $key=>[$name,$phone,$address,$owner]) $companies[$key]=Company::updateOrCreate(['name'=>$name],['phone'=>$phone,'address'=>$address,'owner_id'=>$users[$owner]->id]);
        return $companies;
    }

    private function linkCompanyUsers(array $users,array $companies): void
    {
        foreach (['northstar_owner'=>'northstar','northstar_planner'=>'northstar','summit_owner'=>'summit','summit_planner'=>'summit','prairie_owner'=>'prairie','prairie_planner'=>'prairie','iron_owner'=>'iron','iron_planner'=>'iron','cedar_owner'=>'cedar','cedar_planner'=>'cedar'] as $user=>$company) $users[$user]->update(['company_id'=>$companies[$company]->id]);
    }

    private function skills(): array
    {
        return ['Blueprint Reading','Construction Layout','Site Measurement','Material Takeoffs','Daily Hazard Assessment','Toolbox Talk Leadership','Fall Arrest Equipment Use','Confined Space Procedures','Traffic Accommodation','Housekeeping and Site Cleanup','First Aid Response','Environmental Spill Response','Wood Framing','Steel Stud Framing','Finish Carpentry','Formwork Construction','Door and Hardware Installation','Window Installation','Acoustic Ceiling Installation','Drywall Hanging','Drywall Taping','Drywall Finishing','Firestopping Installation','Cabinet Installation','Commercial Wiring','Residential Wiring','Panel Installation','Conduit Bending','Cable Tray Installation','Motor Controls','Lighting Controls','Electrical Troubleshooting','HVAC Installation','Ductwork Fabrication','Sheet Metal Work','Refrigerant Line Installation','Air Balancing Support','Building Automation Basics','Pipe Fitting','PEX Installation','Copper Soldering','Drainage Rough-In','Fixture Installation','Hydronic Piping','Concrete Placement','Concrete Screeding','Concrete Finishing','Concrete Curing','Rebar Tying','Laser Screed Operation','Curb and Sidewalk Forming','MIG Welding','Stick Welding','Structural Welding','Steel Fit-Up','Bolt-Up Installation','Rigging and Signalperson Duties','Forklift Operation','Skid Steer Operation','Excavator Operation','Loader Operation','Aerial Work Platform Operation','Trench Excavation','Roof Membrane Installation','Shingle Installation','Flashing Installation','Painting and Surface Preparation','Spray Painting','Caulking and Sealants','Concrete Repair','Waterproofing Installation','Scaffolding Assembly','Demolition and Deconstruction','Survey Layout Assistance','Temporary Heating Installation','Insulation Installation','Masonry Repair','Tile Setting','Flooring Installation','Equipment Maintenance Checks','Hot Work Setup','Winter Construction Protection'];
    }

    private function certifications(): array
    {
        return ['WHMIS 2015','CSTS 2020','Standard First Aid / CPR','Emergency First Aid','Fall Protection','Confined Space Entry','Confined Space Rescue Awareness','Fire Extinguisher Training','Ground Disturbance Level II','H2S Alive','Transportation of Dangerous Goods','Traffic Control Person','Red Seal Carpenter','Red Seal Electrician','Red Seal Plumber','Red Seal Welder','Red Seal HVAC Technician','Red Seal Roofer','Red Seal Painter and Decorator','Red Seal Ironworker','Red Seal Concrete Finisher','Red Seal Heavy Equipment Operator','CWB FCAW Qualification','CWB SMAW Qualification','CWB GMAW Qualification','CWB Structural Steel Fit-Up','Elevated Work Platform Operator','Scissor Lift Operator','Boom Lift Operator','Telehandler Operator','Forklift Operator','Skid Steer Operator','Excavator Operator','Front End Loader Operator','Overhead Crane Operator','Rigging Fundamentals','Signalperson Certification','Lockout Tagout','Arc Flash Awareness','Electrical Safety Training System','Propane Handling','Powder Actuated Tool Operator','Silica Awareness','Asbestos Awareness','Lead Awareness','Mold Remediation Awareness','Respiratory Protection Fit Test','Hearing Conservation','Heat Stress Prevention','Winter Driving Safety','Defensive Driving','Construction Safety Officer','Supervisor Safety Training'];
    }

    private function seedLookups(string $model,array $names): array
    {
        $records=[]; foreach ($names as $name) $records[$name]=$model::updateOrCreate(['name'=>$name]); return $records;
    }

    private function seedWorkers(array $users,array $companies,array $skills,array $certifications): array
    {
        $definitions=[
            'liam'=>["Liam O'Connor",'carpenter','northstar',null,12,39.5,['Wood Framing','Steel Stud Framing','Finish Carpentry','Formwork Construction','Blueprint Reading'],['WHMIS 2015','CSTS 2020','Fall Protection','Red Seal Carpenter']],
            'ava'=>['Ava Singh','general_labourer','northstar',null,5,27,['Housekeeping and Site Cleanup','Traffic Accommodation','Material Takeoffs','Daily Hazard Assessment'],['WHMIS 2015','CSTS 2020','Traffic Control Person','Emergency First Aid']],
            'noah'=>['Noah Bennett','drywall_installer','northstar',null,8,34,['Drywall Hanging','Drywall Taping','Drywall Finishing','Steel Stud Framing','Firestopping Installation'],['WHMIS 2015','Fall Protection','Powder Actuated Tool Operator']],
            'olivia'=>['Olivia Martin','roofer','northstar',null,7,33.5,['Roof Membrane Installation','Shingle Installation','Flashing Installation','Fall Arrest Equipment Use'],['WHMIS 2015','Fall Protection','Red Seal Roofer']],
            'maya'=>['Maya Patel','painter','northstar',null,6,32,['Painting and Surface Preparation','Spray Painting','Caulking and Sealants','Blueprint Reading'],['WHMIS 2015','Respiratory Protection Fit Test','Red Seal Painter and Decorator']],
            'priya_worker'=>['Priya Nair','hvac_technician','summit',null,10,42,['HVAC Installation','Ductwork Fabrication','Sheet Metal Work','Refrigerant Line Installation','Air Balancing Support'],['WHMIS 2015','CSTS 2020','Red Seal HVAC Technician','Fall Protection']],
            'daniel_worker'=>['Daniel Brooks','electrician','summit',null,9,43.5,['Commercial Wiring','Panel Installation','Conduit Bending','Motor Controls','Electrical Troubleshooting'],['WHMIS 2015','Red Seal Electrician','Arc Flash Awareness','Lockout Tagout']],
            'mason'=>['Mason Clarke','plumber','summit',null,11,41,['Pipe Fitting','PEX Installation','Copper Soldering','Drainage Rough-In','Fixture Installation'],['WHMIS 2015','Red Seal Plumber','Confined Space Entry','Ground Disturbance Level II']],
            'lucas'=>['Lucas Tremblay','concrete_worker','prairie',null,14,40,['Concrete Placement','Concrete Screeding','Concrete Finishing','Concrete Curing','Rebar Tying'],['WHMIS 2015','CSTS 2020','Red Seal Concrete Finisher','Silica Awareness']],
            'ella'=>['Ella Grant','heavy_equipment','prairie',null,9,44,['Excavator Operation','Loader Operation','Skid Steer Operation','Trench Excavation','Site Measurement'],['WHMIS 2015','Excavator Operator','Front End Loader Operator','Ground Disturbance Level II']],
            'cole'=>['Cole Murphy','welder','iron',null,13,45,['MIG Welding','Stick Welding','Structural Welding','Steel Fit-Up','Rigging and Signalperson Duties'],['WHMIS 2015','Red Seal Welder','CWB FCAW Qualification','CWB SMAW Qualification']],
            'hannah'=>['Hannah Foster','ironworker','iron',null,8,43,['Bolt-Up Installation','Steel Fit-Up','Rigging and Signalperson Duties','Aerial Work Platform Operation','Construction Layout'],['WHMIS 2015','Red Seal Ironworker','Signalperson Certification','Boom Lift Operator']],
            'maria'=>['Maria Chen','carpenter',null,'maria',11,41,['Finish Carpentry','Cabinet Installation','Door and Hardware Installation','Blueprint Reading'],['WHMIS 2015','Red Seal Carpenter','Fall Protection']],
            'jordan'=>['Jordan Lewis','electrician',null,'jordan',7,40,['Commercial Wiring','Lighting Controls','Cable Tray Installation','Electrical Troubleshooting'],['WHMIS 2015','Red Seal Electrician','Electrical Safety Training System']],
            'evan'=>['Evan Cole','welder',null,'evan',15,47.5,['MIG Welding','Structural Welding','Steel Fit-Up','Rigging and Signalperson Duties'],['WHMIS 2015','Red Seal Welder','CWB GMAW Qualification','Confined Space Entry']],
        ];
        $workers=[];
        foreach ($definitions as $key=>[$name,$job,$company,$user,$experience,$rate,$skillNames,$certNames]) {
            $workers[$key]=WorkerProfile::updateOrCreate(['name'=>$name],['user_id'=>$user?$users[$user]->id:null,'company_id'=>$company?$companies[$company]->id:null,'job'=>$job,'years_experience'=>$experience,'hourly_rate'=>$rate]);
            $workers[$key]->skills()->sync(collect($skillNames)->map(fn($name)=>$skills[$name]->id));
            $workers[$key]->certifications()->sync(collect($certNames)->map(fn($name)=>$certifications[$name]->id));
        }
        return $workers;
    }

    private function seedAvailability(array $workers): void
    {
        $monday=now()->startOfWeek()->addWeek();
        foreach (array_keys($workers) as $index=>$key) foreach ([[$index%5,match($index%5){1=>'booked',4=>'unavailable',default=>'available'}],[($index+2)%5,'available']] as [$offset,$status]) {
            $start=$index%3===0?'06:30:00':'07:00:00'; $end=$index%3===0?'15:00:00':'15:30:00';
            Availability::updateOrCreate(['worker_profile_id'=>$workers[$key]->id,'date'=>$monday->copy()->addDays($offset)->toDateString(),'start_time'=>$start],['end_time'=>$end,'status'=>$status]);
        }
    }

    private function seedMissions(array $users,array $companies): array
    {
        $base=now()->startOfWeek(); $missions=[];
        foreach ($this->missionDefinitions() as $key=>$d) {
            $start=$base->copy()->addWeeks($d[10]);
            $missions[$key]=Mission::updateOrCreate(['title'=>$d[2]],['hiring_company_id'=>$companies[$d[0]]->id,'created_by'=>$users[$d[1]]->id,'description'=>$d[3],'city'=>$d[4],'province'=>'Alberta','country'=>'Canada','address_line_1'=>'Project site - details confirmed after acceptance','postal_code'=>'T2P 0A1','site_name'=>$d[5],'directions'=>'Check in with the site superintendent before entering the work area.','job_type'=>$d[6],'workers'=>$d[7],'hourly_rate'=>$d[8],'status'=>$d[9],'start_date'=>$start->toDateString(),'end_date'=>$start->copy()->addWeeks($d[11])->toDateString()]);
            foreach ($d[12] as $requirement) MissionRequirement::updateOrCreate(['mission_id'=>$missions[$key]->id,'name'=>$requirement]);
        }
        return $missions;
    }

    private function missionDefinitions(): array
    {
        $m=fn($company,$creator,$title,$description,$city,$site,$job,$workers,$rate,$status,$start,$duration,$requirements)=>[$company,$creator,$title,$description,$city,$site,$job,$workers,$rate,$status,$start,$duration,$requirements];
        return [
            'framing'=>$m('northstar','northstar_owner','Commercial Framing Crew Needed','Experienced crew needed for metal stud partitions, bulkheads, and door openings in a downtown office renewal.','Calgary','8th Avenue Office Renewal','carpenter',4,42,'open',2,4,['Steel stud framing','Fall Protection','CSTS 2020']),
            'electrician'=>$m('northstar','northstar_planner','Journeyman Electrician for Office Renovation','Complete panel upgrades, lighting controls, and final device installation for a tenant improvement project.','Calgary','Bow Valley Tenant Improvement','electrician',1,46,'open',3,3,['Red Seal Electrician','Commercial wiring experience','WHMIS 2015']),
            'concrete'=>$m('prairie','prairie_owner','Concrete Finishers for Warehouse Slab','Finish a 55,000-square-foot warehouse slab including screeding, edging, curing, and pour-area cleanup.','Red Deer','Gasoline Alley Distribution Centre','concrete_worker',3,43,'open',4,4,['Concrete finishing experience','CSTS 2020','Silica Awareness']),
            'drywall'=>$m('summit','summit_planner','Drywall Installer for Medical Clinic Build-Out','Install board, firestop penetrations, and prepare walls for finishing in a new outpatient clinic.','Edmonton','Westbrook Medical Centre','drywall_installer',2,36,'open',3,5,['Drywall hanging','Firestopping installation','Fall Protection']),
            'roofing'=>$m('cedar','cedar_owner','Commercial Roofing Crew for School Addition','Install SBS membrane, flashings, drains, and edge details on a scheduled school addition.','St. Albert','Riel Park School Addition','roofer',3,40,'open',5,4,['Commercial roofing experience','Fall Protection','WHMIS 2015']),
            'labour'=>$m('iron','iron_planner','General Labourers for Structural Steel Site Support','Support steel erection with material staging, housekeeping, exclusion-zone setup, and traffic control.','Calgary','Eastgate Logistics Hub','general_labourer',3,30,'open',2,3,['Construction site experience','Traffic Control Person','WHMIS 2015']),
            'hvac'=>$m('northstar','northstar_owner','HVAC Installer for Multi-Unit Project','Rough-in and install suite ventilation equipment while coordinating with electrical and framing crews.','Calgary','Kensington Flats','hvac_technician',1,48,'in_progress',-1,5,['Red Seal HVAC Technician','Multi-unit residential experience','Fall Protection']),
            'steel_erection'=>$m('summit','summit_owner','Ironworker for Mechanical Penthouse Steel','Install support steel, complete bolt-up work, and assist with rigging for a rooftop mechanical penthouse.','Edmonton','Jasper Avenue Tower','ironworker',2,49,'in_progress',-2,4,['Rigging Fundamentals','Signalperson Certification','Fall Protection']),
            'plumbing'=>$m('prairie','prairie_planner','Plumber for Recreation Centre Change Rooms','Complete drainage rough-in, domestic water piping, and fixture installation for renovated public change rooms.','Lethbridge','Riverbend Recreation Centre','plumber',1,47,'in_progress',-1,5,['Red Seal Plumber','Drainage rough-in','Confined Space Entry']),
            'painting'=>$m('cedar','cedar_planner','Painter for Apartment Corridor Refresh','Prepare surfaces and apply durable coatings to occupied apartment corridors during daytime access windows.','Edmonton','Strathcona Court Apartments','painter',2,35,'in_progress',-1,3,['Painting and surface preparation','Respiratory Protection Fit Test','WHMIS 2015']),
            'excavation'=>$m('iron','iron_owner','Heavy Equipment Operator for Utility Trenching','Operate excavator and skid steer for utility trenching, bedding, backfill, and site restoration.','Cochrane','Sunset Ridge Utility Expansion','heavy_equipment',1,50,'in_progress',-2,4,['Excavator Operator','Ground Disturbance Level II','Traffic Accommodation']),
            'heritage_carpentry'=>$m('northstar','northstar_planner','Finish Carpenter for Heritage Window Restoration','Planning scope for trim replication, sash repairs, and interior wood finishing at a heritage property.','Calgary','Macleod House Restoration','carpenter',1,44,'draft',7,5,['Finish carpentry','Heritage renovation experience','WHMIS 2015']),
            'hospital_wiring'=>$m('summit','summit_owner','Electrical Crew for Hospital Wing Fit-Out','Preliminary posting for cable tray, branch wiring, and low-voltage coordination in a hospital expansion.','Edmonton','Northgate Hospital Expansion','electrician',4,49,'draft',8,10,['Commercial wiring','Electrical Safety Training System','CSTS 2020']),
            'parking_slab'=>$m('prairie','prairie_owner','Concrete Crew for Parkade Ramp Repairs','Planning scope for removal, rebar placement, forming, and replacement of a deteriorated parkade ramp.','Red Deer','Parkland Centre Parkade','concrete_worker',3,44,'draft',6,3,['Concrete repair experience','Silica Awareness','WHMIS 2015']),
            'warehouse_racking'=>$m('iron','iron_planner','Welder for Warehouse Racking Modifications','Pending scope for structural modifications and field welding to existing warehouse storage racks.','Calgary','Crossroads Fulfillment Warehouse','welder',1,51,'draft',6,3,['CWB welding qualification','Structural welding experience','Fall Protection']),
            'exterior_sealant'=>$m('cedar','cedar_owner','Exterior Caulking Technician for Curtain Wall Repairs','Preconstruction posting for sealant removal, joint preparation, and replacement at a commercial curtain wall.','Edmonton','Gateway Centre','painter',1,39,'draft',9,4,['Caulking and sealants','Boom Lift Operator','Fall Protection']),
            'structural_welder'=>$m('iron','iron_owner','Structural Steel Welder','Completed fabrication and field repair scope at an industrial expansion, including CWB-compliant welds.','Calgary','Foothills Industrial Expansion','welder',1,52,'completed',-8,4,['Red Seal Welder','Structural welding experience','Fall Protection']),
            'retail_carpentry'=>$m('summit','summit_planner','Finish Carpenter for Retail Millwork Installation','Completed installation of counters, feature walls, hardware, and protective finishes for a retail opening.','Edmonton','Oliver Square Retail Fit-Out','carpenter',1,43,'completed',-9,3,['Finish carpentry','Cabinet installation','Blueprint Reading']),
            'drain_piping'=>$m('prairie','prairie_owner','Plumber for Industrial Drainage Upgrade','Completed replacement of floor drains, cleanouts, and connecting pipe in an operating fabrication shop.','Red Deer','Riverside Fabrication Plant','plumber',1,46,'completed',-10,3,['Drainage rough-in','Confined Space Entry','Red Seal Plumber']),
            'boom_operator'=>$m('iron','iron_planner','Aerial Lift Operator for Building Envelope Repairs','Completed elevated access support for cladding repairs, sealant removal, and material staging.','Calgary','Westpoint Business Centre','heavy_equipment',1,45,'completed',-7,2,['Boom Lift Operator','Fall Protection','Traffic Accommodation']),
            'roof_repair'=>$m('cedar','cedar_owner','Roofer for Emergency Membrane Repairs','Completed emergency membrane patches and flashing repairs following a spring wind event.','Edmonton','Mill Creek Community Hall','roofer',2,42,'completed',-6,2,['Roof membrane installation','Fall Protection','WHMIS 2015']),
            'curbing'=>$m('northstar','northstar_owner','Concrete Finisher for Sidewalk and Curb Package','Completed forming, placement, broom finishing, and curing for site sidewalks and curb returns.','Calgary','Aspen Landing Commercial Site','concrete_worker',2,41,'completed',-9,3,['Concrete finishing','Curb and sidewalk forming','Silica Awareness']),
            'generator_electrical'=>$m('summit','summit_owner','Electrician for Emergency Generator Tie-In','Completed conduit, feeder, grounding, and commissioning support for an emergency generator tie-in.','Edmonton','Meadowlark Seniors Residence','electrician',1,50,'completed',-11,3,['Commercial wiring','Conduit bending','Arc Flash Awareness']),
            'bridge_labour'=>$m('prairie','prairie_planner','General Labourer for Bridge Maintenance Access','Completed access setup, traffic accommodation, cleanup, and material support for bridge maintenance crews.','Lethbridge','Oldman River Bridge','general_labourer',2,31,'completed',-8,2,['Traffic accommodation','Housekeeping and site cleanup','Fall Protection']),
            'stair_ironwork'=>$m('iron','iron_owner','Ironworker for Exterior Stair Tower Installation','Completed steel stair tower erection, bolt-up, handrail installation, and final demobilization.','Calgary','Northeast Transit Garage','ironworker',2,50,'completed',-10,3,['Bolt-up installation','Rigging and signalperson duties','Fall Protection']),
            'suite_painting'=>$m('cedar','cedar_planner','Painter for Multi-Unit Suite Turnover','Completed patching, preparation, and repainting before tenant occupancy.','Edmonton','Garneau Residences','painter',2,34,'completed',-7,3,['Painting and surface preparation','Spray painting','WHMIS 2015']),
            'cancelled_roof'=>$m('northstar','northstar_owner','Roofing Crew for Community Centre Addition','Cancelled after the general contractor consolidated work with its existing trade partner.','Airdrie','Meadowbrook Community Centre','roofer',2,38,'cancelled',8,2,['Fall Protection','Commercial roofing experience']),
            'cancelled_duct'=>$m('summit','summit_planner','Sheet Metal Installer for Restaurant Ventilation','Cancelled after the tenant deferred the restaurant build-out to a later phase.','Edmonton','Whyte Avenue Restaurant Fit-Out','hvac_technician',1,44,'cancelled',5,3,['Ductwork fabrication','Sheet metal work','WHMIS 2015']),
            'cancelled_concrete'=>$m('prairie','prairie_owner','Concrete Labourers for Retaining Wall Pour','Cancelled because site servicing work moved into the next construction season.','Red Deer','Timberlands Subdivision','general_labourer',3,29,'cancelled',6,2,['Concrete placement','Site cleanup','CSTS 2020']),
            'cancelled_welding'=>$m('iron','iron_planner','Welder for Agricultural Building Repairs','Cancelled after the owner elected to replace the damaged building section.','Okotoks','Foothills Agricultural Storage','welder',1,48,'cancelled',7,2,['MIG welding','Fall Protection','WHMIS 2015']),
        ];
    }

    private function seedActivity(array $users,array $companies,array $workers,array $missions): void
    {
        $rows=[
            ['framing','maria','northstar_owner','northstar','invite','pending','Your commercial framing experience is a strong match for this Calgary office project.'],['electrician','jordan','jordan',null,'apply','pending','I am available for the renovation schedule and have current tenant-improvement experience.'],['concrete','ava','northstar_planner','northstar','apply','pending','Northstar is proposing Ava for the slab-placement scope.'],['drywall','noah','northstar_planner','northstar','apply','pending','Noah has commercial drywall and firestopping experience.'],['roofing','olivia','northstar_owner','northstar','apply','pending','Olivia is available for membrane and flashing work.'],['labour','ava','northstar_planner','northstar','apply','pending','Ava is available for site support and traffic accommodation duties.'],
            ['framing','cole','northstar_owner','northstar','invite','rejected','We would like to discuss Cole’s availability for the framing crew.','iron_planner'],['electrician','daniel_worker','summit_planner','summit','apply','rejected','Daniel is available for the office-renovation electrical scope.','northstar_owner'],['concrete','maria','maria',null,'apply','rejected','I have recent commercial slab and formwork experience.','prairie_owner'],['drywall','hannah','iron_planner','iron','apply','rejected','Hannah can support steel stud layout and trade coordination.','summit_owner'],['roofing','evan','evan',null,'apply','rejected','I am available to assist with exterior repair work.','cedar_owner'],
            ['hvac','priya_worker','summit_planner','summit','apply','accepted','Priya is available for the listed multi-unit HVAC installation scope.','northstar_owner'],['steel_erection','hannah','iron_planner','iron','apply','accepted','Hannah is available for the penthouse steel installation schedule.','summit_owner'],['steel_erection','cole','summit_planner','summit','invite','accepted','We would like to invite Cole to support the mechanical penthouse steel scope.','iron_owner'],['plumbing','mason','summit_planner','summit','apply','accepted','Mason can complete the drainage and fixture package.','prairie_owner'],['painting','maya','northstar_owner','northstar','apply','accepted','Maya is available for occupied-corridor preparation and painting.','cedar_owner'],['excavation','ella','prairie_planner','prairie','apply','accepted','Ella is available with excavator and skid steer experience.','iron_owner'],
            ['structural_welder','evan','evan',null,'apply','completed','I am a Red Seal welder with current structural field experience.','iron_owner'],['retail_carpentry','maria','summit_planner','summit','invite','completed','We would like to invite Maria for the retail millwork installation package.','maria'],['drain_piping','mason','summit_planner','summit','apply','completed','Mason is available for the industrial drainage upgrade.','prairie_owner'],['boom_operator','ella','iron_planner','iron','invite','completed','We would like to invite Ella to support the envelope-repair access work.','prairie_planner'],['roof_repair','olivia','northstar_owner','northstar','apply','completed','Olivia can respond to the emergency membrane repair package.','cedar_owner'],['curbing','lucas','prairie_planner','prairie','apply','completed','Lucas is available for sidewalk and curb finishing.','northstar_owner'],['generator_electrical','jordan','jordan',null,'apply','completed','I have generator tie-in and commercial feeder experience.','summit_owner'],['bridge_labour','ava','northstar_planner','northstar','apply','completed','Ava is available for bridge access and traffic support duties.','prairie_planner'],['stair_ironwork','evan','evan',null,'apply','completed','I am available for stair tower steel fit-up and site welding.','iron_owner'],['suite_painting','maya','northstar_owner','northstar','apply','completed','Maya can complete the suite turnover painting package.','cedar_planner'],
        ];
        foreach ($rows as $row) {
            [$mission,$worker,$requester,$company,$type,$status,$message,$responder]=array_pad($row,8,null); $responded=in_array($status,['accepted','rejected','completed'],true);
            WorkerRequest::updateOrCreate(['mission_id'=>$missions[$mission]->id,'worker_profile_id'=>$workers[$worker]->id,'type'=>$type],['mission_id'=>$missions[$mission]->id,'requested_by'=>$users[$requester]->id,'company_id'=>$company?$companies[$company]->id:null,'worker_profile_id'=>$workers[$worker]->id,'type'=>$type,'message'=>$message,'status'=>$status,'responded_by'=>$responder?$users[$responder]->id:null,'responded_at'=>$responded?now()->subDays(5):null,'completed_at'=>$status==='completed'?now()->subWeeks(2):null]);
        }
        foreach (['structural_welder'=>['evan','iron_owner',5,'Evan was dependable, safety-focused, and completed the repair scope to a high standard.'],'retail_carpentry'=>['maria','summit_owner',5,'Maria delivered careful finish work and coordinated well with the retail opening schedule.'],'drain_piping'=>['mason','prairie_owner',4,'Mason completed the drainage work cleanly and communicated issues early.'],'boom_operator'=>['ella','iron_planner',4,'Ella operated safely and kept material access moving through a tight work area.'],'roof_repair'=>['olivia','cedar_owner',5,'Olivia responded quickly and completed repairs ahead of the weather window.'],'curbing'=>['lucas','northstar_owner',3,'The curb work met the required finish, though the crew needed more schedule follow-up.'],'generator_electrical'=>['jordan','summit_owner',5,'Jordan was well prepared, followed the commissioning plan, and produced excellent documentation.'],'bridge_labour'=>['ava','prairie_planner',3,'Ava was reliable on site and completed the assigned access and cleanup duties.'],'stair_ironwork'=>['evan','iron_owner',2,'The steel work was acceptable, but late arrivals created coordination delays.'],'suite_painting'=>['maya','cedar_planner',2,'Surface preparation required rework before final turnover, although the final coating was acceptable.']] as $mission=>[$worker,$reviewer,$score,$feedback]) {
            Rating::updateOrCreate(['mission_id'=>$missions[$mission]->id],['reviewed_by_user_id'=>$users[$reviewer]->id,'worker_profile_id'=>$workers[$worker]->id,'score'=>$score,'feedback'=>$feedback]);
        }
    }
}
