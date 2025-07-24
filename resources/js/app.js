import "./bootstrap";
import "../css/app.css";
import "primeicons/primeicons.css";
// import '../css/tailwind.css';
import "../css/sakai/layout.scss";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura";
// import router from './router/index.js';

// import ConfirmationService from 'primevue/confirmationservice';
// import Accordion from 'primevue/accordion';
// import AccordionContent from 'primevue/accordioncontent';
// import AccordionHeader from 'primevue/accordionheader';
// import AccordionPanel from 'primevue/accordionpanel';
// import Avatar from 'primevue/avatar';
// import AvatarGroup from 'primevue/avatargroup';   //Optional for grouping
// import AutoComplete from 'primevue/autocomplete';
// import Badge from 'primevue/badge';
// import BlockUI from 'primevue/blockui';
// import Breadcrumb from 'primevue/breadcrumb';
import Button from "primevue/button";
// import Card from 'primevue/card';
import Carousel from "primevue/carousel";
// import Calendar from 'primevue/calendar';
// import CascadeSelect from 'primevue/cascadeselect';
import Chart from "primevue/chart";
// import Chip from 'primevue/chip';
// import ConfirmDialog from 'primevue/confirmdialog';
// import ConfirmPopup from 'primevue/confirmpopup';
// import ContextMenu from 'primevue/contextmenu';
// import ColorPicker from 'primevue/colorpicker';
import Column from "primevue/column";
import DataTable from "primevue/datatable";
// import DataView from 'primevue/dataview';
// import DatePicker from 'primevue/datepicker';
// import DeferredContent from 'primevue/deferredcontent';
import Dialog from "primevue/dialog";
// import Divider from 'primevue/divider';
// import Dock from 'primevue/dock';
// import Drawer from 'primevue/drawer';
// import DynamicDialog from 'primevue/dynamicdialog';
import Editor from "primevue/editor";
// import FileUpload from 'primevue/fileupload';
// import FloatLabel from 'primevue/floatlabel';
// import Fluid from 'primevue/fluid';
// import Galleria from 'primevue/galleria';
import IconField from "primevue/iconfield";
// import Image from 'primevue/image';
// import Inplace from 'primevue/inplace';
// import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from "primevue/inputgroupaddon";
import InputIcon from "primevue/inputicon";
// import InputMask from 'primevue/inputmask';
import InputNumber from "primevue/inputnumber";
// import InputOtp from 'primevue/inputotp';
// import Knob from 'primevue/knob';
// import Listbox from 'primevue/listbox';
// import MegaMenu from 'primevue/megamenu';
import Menu from "primevue/menu";
// import Menubar from 'primevue/menubar';
// import MeterGroup from 'primevue/metergroup';
// import Message from 'primevue/message';
// import MultiSelect from 'primevue/multiselect';
// import OrderList from 'primevue/orderlist';
// import OrganizationChart from 'primevue/organizationchart';
// import OverlayBadge from 'primevue/overlaybadge';
// import OverlayPanel from 'primevue/overlaypanel';
// import Panel from 'primevue/panel';
// import PanelMenu from 'primevue/panelmenu';
// import Paginator from 'primevue/paginator';
// import Password from 'primevue/password';
// import PickList from 'primevue/picklist';
// import Popover from 'primevue/popover';
// import ProgressBar from 'primevue/progressbar';
// import ProgressSpinner from 'primevue/progressspinner';
import Rating from "primevue/rating";
// import ScrollPanel from 'primevue/scrollpanel';
// import ScrollTop from 'primevue/scrolltop';
import Select from "primevue/select";
import StyleClass from "primevue/styleclass";
import SelectButton from "primevue/selectbutton";
// import Slider from 'primevue/slider';
// import SpeedDial from 'primevue/speeddial';
// import SplitButton from 'primevue/splitbutton';
// import Splitter from 'primevue/splitter';
// import SplitterPanel from 'primevue/splitterpanel';
import Step from "primevue/step";
import StepItem from "primevue/stepitem";
import StepList from "primevue/steplist";
import StepPanel from "primevue/steppanel";
import Stepper from "primevue/stepper";
// import Tab from 'primevue/tab';
// import TabList from 'primevue/tablist';
// import TabPanel from 'primevue/tabpanel';
// import TabPanels from 'primevue/tabpanels';
// import Tabs from 'primevue/tabs';
import Tag from "primevue/tag";
// import TieredMenu from 'primevue/tieredmenu';
// import Timeline from 'primevue/timeline';
import Toast from "primevue/toast";
import ToastService from "primevue/toastservice";
// import ToggleButton from 'primevue/togglebutton';
// import ToggleSwitch from 'primevue/toggleswitch';
import Toolbar from "primevue/toolbar";
import Tooltip from "primevue/tooltip";
// import Tree from 'primevue/tree';
// import TreeTable from 'primevue/treetable';
// import TreeSelect from 'primevue/treeselect';
// import VirtualScroller from 'primevue/virtualscroller';
// End Prime Components

const appName = import.meta.env.VITE_APP_NAME || "Procomsa";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return (
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(PrimeVue, {
                    theme: {
                        preset: Aura,
                        options: {
                            darkModeSelector: ".app-dark",
                        },
                    },
                })
                // .component('Accordion', Accordion)
                // .component('AccordionContent', AccordionContent)
                // .component('AccordionHeader', AccordionHeader)
                // .component('AccordionPanel', AccordionPanel)
                // .component('Avatar', Avatar)
                // .component('AvatarGroup', AvatarGroup)
                // .component('AutoComplete', AutoComplete)
                // .component('Badge', Badge)
                // .component('BlockUI', BlockUI)
                // .component('Breadcrumb', Breadcrumb)
                .component("Button", Button)
                // .component('Card', Card)
                .component("Carousel", Carousel)
                // .component('Calendar', Calendar)
                // .component('CascadeSelect', CascadeSelect)
                .component("Chart", Chart)
                // .component('Chip', Chip)
                // .component('ConfirmDialog', ConfirmDialog)
                // .component('ConfirmPopup', ConfirmPopup)
                // .component('ContextMenu', ContextMenu)
                // .component('ColorPicker', ColorPicker)
                .component("Column", Column)
                .component("DataTable", DataTable)
                // .component('DataView', DataView)
                // .component('DatePicker', DatePicker)
                // .component('DeferredContent', DeferredContent)
                .component("Dialog", Dialog)
                // .component('Divider', Divider)
                // .component('Dock', Dock)
                // .component('Drawer', Drawer)
                // .component('DynamicDialog', DynamicDialog)
                .component("Editor", Editor)
                // .component('FileUpload', FileUpload)
                // .component('FloatLabel', FloatLabel)
                // .component('Fluid', Fluid)
                // .component('Galleria', Galleria)
                .component("IconField", IconField)
                // .component('Image', Image)
                // .component('Inplace', Inplace)
                // .component('InputGroup', InputGroup)
                .component("InputGroupAddon", InputGroupAddon)
                .component("InputIcon", InputIcon)
                // .component('InputMask', InputMask)
                .component("InputNumber", InputNumber)
                // .component('InputOtp', InputOtp)
                // .component('Knob', Knob)
                // .component('Listbox', Listbox)
                // .component('MegaMenu', MegaMenu)
                .component("Menu", Menu)
                // .component('Menubar', Menubar)
                // .component('MeterGroup', MeterGroup)
                // .component('Message', Message)
                // .component('MultiSelect', MultiSelect)
                // .component('OrderList', OrderList)
                // .component('OrganizationChart', OrganizationChart)
                // .component('OverlayBadge', OverlayBadge)
                // .component('OverlayPanel', OverlayPanel)
                // .component('Panel', Panel)
                // .component('PanelMenu', PanelMenu)
                // .component('Paginator', Paginator)
                // .component('Password', Password)
                // .component('PickList', PickList)
                // .component('Popover', Popover)
                // .component('ProgressBar', ProgressBar)
                // .component('ProgressSpinner', ProgressSpinner)
                .component("Rating", Rating)
                // .component('ScrollPanel', ScrollPanel)
                // .component('ScrollTop', ScrollTop)
                .component("Select", Select)
                .component("SelectButton", SelectButton)
                // .component('Slider', Slider)
                // .component('SpeedDial', SpeedDial)
                // .component('SplitButton', SplitButton)
                // .component('Splitter', Splitter)
                // .component('SplitterPanel', SplitterPanel)
                .component("Step", Step)
                .component("StepItem", StepItem)
                .component("StepList", StepList)
                .component("StepPanel", StepPanel)
                .component("Stepper", Stepper)
                // .component('Tab', Tab)
                // .component('TabList', TabList)
                // .component('TabPanel', TabPanel)
                // .component('TabPanels', TabPanels)
                // .component('Tabs', Tabs)
                .component("Tag", Tag)
                // .component('TieredMenu', TieredMenu)
                // .component('Timeline', Timeline)
                .component("Toast", Toast)
                // .component('ToggleButton', ToggleButton)
                // .component('ToggleSwitch', ToggleSwitch)
                .component("Toolbar", Toolbar)
                // .component('Tree', Tree)
                // .component('TreeTable', TreeTable)
                // .component('TreeSelect', TreeSelect)
                // .component('VirtualScroller', VirtualScroller)
                .directive("styleclass", StyleClass)
                .directive("tooltip", Tooltip)
                .use(ToastService)
                // .use(router)
                .use(ZiggyVue)
                .mount(el)
        );
    },
    progress: {
        color: "#4B5563",
    },
});
